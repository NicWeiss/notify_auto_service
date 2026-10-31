package delivery_service

import (
	"errors"
	"fmt"

	"golang.org/x/time/rate"

	"notifier/src/core"
	"notifier/src/integrations/firebase"
	"notifier/src/integrations/mail"
	"notifier/src/integrations/telegram"
	"notifier/src/repo/models"
	"notifier/src/services/fcm_service"
)

// Handler delivers messages of one channel. Every worker has its own handler,
// so it may keep a connection and needs no locking.
type Handler interface {
	Deliver(delivery models.Delivery) error
	Close()
}

type HandlerFactory func() Handler

// Channel is a type of messages with its own queue and workers.
type Channel struct {
	Type       string
	Workers    int
	NewHandler HandlerFactory
}

// DefaultChannels are the real senders, configured from the environment.
func DefaultChannels() []Channel {
	telegramLimit := rate.NewLimiter(rate.Limit(core.Cfg.TelegramRate), int(core.Cfg.TelegramRate))

	return []Channel{
		{
			Type:       "email",
			Workers:    core.Cfg.EmailWorkers,
			NewHandler: func() Handler { return newEmailHandler() },
		},
		{
			Type:       "tg",
			Workers:    core.Cfg.TelegramWorkers,
			NewHandler: func() Handler { return &telegramHandler{limit: telegramLimit} },
		},
		{
			Type:       "push",
			Workers:    core.Cfg.PushWorkers,
			NewHandler: func() Handler { return &pushHandler{} },
		},
	}
}

// --- email ---

type emailHandler struct {
	session *mail.Session
	from    mail.Address
	err     error
}

func newEmailHandler() *emailHandler {
	handler := &emailHandler{from: mail.Address{Name: core.Cfg.SenderName, Email: core.Cfg.SenderEmail}}

	client, err := mail.NewClient(core.Cfg.SMTPURI)
	if err != nil {
		handler.err = err
	} else {
		handler.session = client.NewSession()
	}

	return handler
}

func (h *emailHandler) Deliver(delivery models.Delivery) error {
	if h.err != nil {
		return permanent(h.err)
	}

	err := h.session.Send(h.from, []string{delivery.Account}, delivery.Title, delivery.Body, "")
	if err != nil && mail.IsPermanent(err) {
		return permanent(err)
	}

	return err
}

func (h *emailHandler) Close() {
	if h.session != nil {
		h.session.Close()
	}
}

// --- telegram ---

type telegramHandler struct {
	limit *rate.Limiter // shared by all workers: Telegram limits the bot, not a connection
}

func (h *telegramHandler) Deliver(delivery models.Delivery) error {
	if core.Cfg.TelegramToken == "" {
		return permanent(telegram.ErrNotConfigured)
	}

	h.limit.Wait(backgroundContext)

	err := telegram.Send(core.Cfg.TelegramToken, delivery.Account, delivery.Title, delivery.Body)

	var answer *telegram.Error
	if errors.As(err, &answer) {
		if answer.Permanent() {
			return permanent(err)
		}
		if answer.RetryAfter > 0 {
			return &retryAfterError{err: err, after: answer.RetryAfter}
		}
	}

	return err
}

func (h *telegramHandler) Close() {}

// --- push ---

type pushHandler struct{}

func (h *pushHandler) Deliver(delivery models.Delivery) error {
	err := firebase.SendPush(core.Cfg.FCMServerKey, delivery.Account, delivery.Title, delivery.Body)

	switch {
	case errors.Is(err, firebase.ErrNotConfigured):
		return permanent(err)

	case errors.Is(err, firebase.ErrInvalidToken):
		// the device is gone, forget the token
		if removeErr := fcm_service.RemoveToken(delivery.UserId, delivery.Account); removeErr != nil {
			return permanent(fmt.Errorf("%w (token is not removed: %v)", err, removeErr))
		}
		return permanent(err)
	}

	return err
}

func (h *pushHandler) Close() {}
