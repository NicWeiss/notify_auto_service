package telegram

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	"net"
	"net/http"
	"time"
)

// ErrNotConfigured: there is no bot token.
var ErrNotConfigured = errors.New("TELERGAM_BOT_TOKEN is not configured")

// one client for all workers: connections to Telegram are reused
var httpClient = &http.Client{
	Timeout: 10 * time.Second,
	Transport: &http.Transport{
		DialContext:         (&net.Dialer{Timeout: 5 * time.Second, KeepAlive: 30 * time.Second}).DialContext,
		MaxIdleConns:        64,
		MaxIdleConnsPerHost: 64,
		IdleConnTimeout:     90 * time.Second,
		ForceAttemptHTTP2:   true,
	},
}

// Error is an answer of Telegram which is not 200.
type Error struct {
	Status      int
	Description string
	RetryAfter  time.Duration // set when the limit is exceeded (429)
}

func (e *Error) Error() string {
	return fmt.Sprintf("telegram answered %d: %s", e.Status, e.Description)
}

// Permanent: the chat does not exist, the bot is blocked, the text is bad...
func (e *Error) Permanent() bool {
	return e.Status >= 400 && e.Status < 500 && e.Status != http.StatusTooManyRequests
}

func Send(token string, chatId string, title string, text string) error {
	if token == "" {
		return ErrNotConfigured
	}

	payload, err := json.Marshal(map[string]string{
		"chat_id": chatId,
		"text":    fmt.Sprintf("%s \n %s", title, text),
	})
	if err != nil {
		return err
	}

	response, err := httpClient.Post(
		fmt.Sprintf("https://api.telegram.org/bot%s/sendMessage", token),
		"application/json",
		bytes.NewReader(payload),
	)
	if err != nil {
		// the url contains the bot token, it must not get into logs
		return errors.New("telegram request failed")
	}
	defer response.Body.Close()

	if response.StatusCode == http.StatusOK {
		return nil
	}

	var answer struct {
		Description string `json:"description"`
		Parameters  struct {
			RetryAfter int `json:"retry_after"`
		} `json:"parameters"`
	}
	json.NewDecoder(response.Body).Decode(&answer)

	return &Error{
		Status:      response.StatusCode,
		Description: answer.Description,
		RetryAfter:  time.Duration(answer.Parameters.RetryAfter) * time.Second,
	}
}
