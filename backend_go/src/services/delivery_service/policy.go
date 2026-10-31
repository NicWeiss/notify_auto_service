package delivery_service

import (
	"errors"
	"time"

	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

const maxAttempts = 5

// delays between attempts, indexed by the number of attempts already made
var retryDelays = []time.Duration{10 * time.Second, time.Minute, 5 * time.Minute, 30 * time.Minute}

func retryDelay(attempts int) time.Duration {
	if attempts < 1 {
		attempts = 1
	}
	if attempts > len(retryDelays) {
		attempts = len(retryDelays)
	}

	return retryDelays[attempts-1]
}

// permanentError: a retry can't help (bad address, blocked bot, no config).
type permanentError struct{ err error }

func (e *permanentError) Error() string { return e.err.Error() }
func (e *permanentError) Unwrap() error { return e.err }

func permanent(err error) error {
	if err == nil {
		return nil
	}

	return &permanentError{err: err}
}

// retryAfterError asks to wait at least After before the next attempt.
type retryAfterError struct {
	err   error
	after time.Duration
}

func (e *retryAfterError) Error() string { return e.err.Error() }
func (e *retryAfterError) Unwrap() error { return e.err }

// outcome is the decision about a delivery after an attempt.
type outcome struct {
	status string // crud.DeliveryDone, DeliveryNew (retry) or DeliveryFailed
	delay  time.Duration
	reason string
}

func decide(delivery models.Delivery, err error) outcome {
	if err == nil {
		return outcome{status: crud.DeliveryDone}
	}

	var forbidden *permanentError
	if errors.As(err, &forbidden) {
		return outcome{status: crud.DeliveryFailed, reason: err.Error()}
	}
	if delivery.Attempts >= maxAttempts {
		return outcome{status: crud.DeliveryFailed, reason: "attempts are over: " + err.Error()}
	}

	delay := retryDelay(delivery.Attempts)

	var asked *retryAfterError
	if errors.As(err, &asked) && asked.after > delay {
		delay = asked.after
	}

	return outcome{status: crud.DeliveryNew, delay: delay, reason: err.Error()}
}
