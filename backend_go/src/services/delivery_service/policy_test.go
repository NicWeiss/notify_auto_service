package delivery_service

import (
	"errors"
	"fmt"
	"testing"
	"time"

	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

func TestDecide(t *testing.T) {
	temporary := errors.New("connection reset")

	cases := []struct {
		name       string
		attempts   int
		err        error
		wantStatus string
		wantDelay  time.Duration
	}{
		{"sent", 1, nil, crud.DeliveryDone, 0},
		{"first failure is retried soon", 1, temporary, crud.DeliveryNew, 10 * time.Second},
		{"delay grows", 3, temporary, crud.DeliveryNew, 5 * time.Minute},
		{"delay is limited", 4, temporary, crud.DeliveryNew, 30 * time.Minute},
		{"attempts are over", maxAttempts, temporary, crud.DeliveryFailed, 0},
		{"permanent error is not retried", 1, permanent(temporary), crud.DeliveryFailed, 0},
		{"wrapped permanent error", 1, fmt.Errorf("x: %w", permanent(temporary)), crud.DeliveryFailed, 0},
		{"server asks to wait", 1, &retryAfterError{err: temporary, after: time.Minute}, crud.DeliveryNew, time.Minute},
		{"short retry-after does not shorten the pause", 3, &retryAfterError{err: temporary, after: time.Second}, crud.DeliveryNew, 5 * time.Minute},
	}

	for _, c := range cases {
		got := decide(models.Delivery{Attempts: c.attempts}, c.err)

		if got.status != c.wantStatus || got.delay != c.wantDelay {
			t.Errorf("%s: got %s/%s, want %s/%s", c.name, got.status, got.delay, c.wantStatus, c.wantDelay)
		}
	}
}
