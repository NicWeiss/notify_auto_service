// Package notify_send_service is the matcher: it looks for the notifies which
// have to be sent at a minute and puts the messages into the outbox. The
// delivery itself is done by delivery_service.
package notify_send_service

import (
	"database/sql"
	"log"
	"strings"
	"time"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/date_operation_service"
)

const claimBatch = 20

// plan is everything what has to be written for one minute.
type plan struct {
	deliveries []models.Delivery

	// notifies which fire every N minutes/hours get their next time
	nextTimeIds []int64
	nextTimes   []string

	// notifies with the passed autodisable date
	disableIds []int64
}

// nextTime shifts "HH:MM" by the interval, wrapping around midnight.
func nextTime(current string, interval time.Duration) (string, error) {
	parsed, err := time.Parse("15:04", current)
	if err != nil {
		return "", err
	}

	return parsed.Add(interval).Format("15:04"), nil
}

// buildPlan is a pure function: it makes no queries.
func buildPlan(notifies []models.Notify, now time.Time) plan {
	var result plan

	for _, notify := range notifies {
		if len(notify.Acceptors) == 0 {
			continue
		}

		if notify.RepeateInterval != nil && (notify.Periodic == "every_several_minutes" || notify.Periodic == "every_several_hours") {
			interval := time.Duration(*notify.RepeateInterval) * time.Minute
			if notify.Periodic == "every_several_hours" {
				interval = time.Duration(*notify.RepeateInterval) * time.Hour
			}

			if next, err := nextTime(notify.Time, interval); err != nil {
				log.Printf("Notify %d has a wrong time %q: %v", notify.Id, notify.Time, err)
			} else {
				result.nextTimeIds = append(result.nextTimeIds, int64(notify.Id))
				result.nextTimes = append(result.nextTimes, next)
			}
		}

		// the last message is still sent, then the notify is switched off
		if notify.IsAutodisable && notify.AutodisableAt != nil && now.After(*notify.AutodisableAt) {
			result.disableIds = append(result.disableIds, int64(notify.Id))
		}

		body := ""
		if notify.Text != nil {
			body = *notify.Text
		}

		for _, acceptor := range notify.Acceptors {
			for _, account := range accounts(acceptor) {
				result.deliveries = append(result.deliveries, models.Delivery{
					NotifyId: notify.Id,
					UserId:   notify.UserId,
					Type:     acceptor.SystemType,
					Account:  account,
					Title:    notify.Name,
					Body:     body,
				})
			}
		}
	}

	return result
}

// accounts splits an acceptor into recipients: a push acceptor keeps many
// device tokens separated by ';'.
func accounts(acceptor models.Acceptor) []string {
	if acceptor.SystemType != "push" {
		if acceptor.Account == "" {
			return nil
		}
		return []string{acceptor.Account}
	}

	tokens := make([]string, 0)
	for _, token := range strings.Split(acceptor.Account, ";") {
		if token != "" {
			tokens = append(tokens, token)
		}
	}

	return tokens
}

// ProcessOperation matches one minute and writes the result in one
// transaction: either the operation is done and its messages are queued, or
// nothing has happened and the operation can be repeated.
func ProcessOperation(operation models.DateOperation) (int, error) {
	queued := 0

	err := core.InTx(func(tx *sql.Tx) error {
		notifies, err := crud.FindNotifiesForMinute(tx, operation.DateData)
		if err != nil {
			return err
		}

		now := time.Now()
		result := buildPlan(notifies, now)

		if err = crud.UpdateNotifyTimes(tx, result.nextTimeIds, result.nextTimes); err != nil {
			return err
		}
		if err = crud.DisableNotifies(tx, result.disableIds); err != nil {
			return err
		}
		if err = crud.InsertDeliveries(tx, result.deliveries, now); err != nil {
			return err
		}

		queued = len(result.deliveries)
		return crud.FinishDateOperation(tx, operation.Id, crud.StatusDone, now)
	})

	return queued, err
}

// ProcessPending takes all new operations (the minutes which have come) and
// queues their messages. It returns the number of queued messages.
func ProcessPending() (int, error) {
	total := 0

	for {
		operations, err := date_operation_service.ClaimOperations(claimBatch)
		if err != nil {
			return total, err
		}

		for _, operation := range operations {
			queued, err := ProcessOperation(operation)
			if err != nil {
				log.Printf("Operation %d is fail: %v", operation.Id, err)

				if err = date_operation_service.FinishOperation(operation.Id, true); err != nil {
					log.Printf("Can't save status of operation %d: %v", operation.Id, err)
				}
				continue
			}

			if queued > 0 {
				log.Printf("Operation %d (%s %s): %d messages queued",
					operation.Id, operation.DateData.Date, operation.DateData.Time, queued)
			}
			total += queued
		}

		if len(operations) < claimBatch {
			return total, nil
		}
	}
}
