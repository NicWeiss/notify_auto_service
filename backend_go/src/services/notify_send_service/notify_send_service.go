// Package notify_send_service is the matcher: it looks for the notifies which
// have to be sent at a minute and puts the messages into the outbox. The
// delivery itself is done by delivery_service.
package notify_send_service

import (
	"database/sql"
	"log"
	"sort"
	"strings"
	"time"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/date_operation_service"
	"notifier/src/services/delivery_service"
)

const claimBatch = 200

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

// match finds the notifies of a minute and applies the changes which do not
// depend on the delivery (next time, autodisable). The messages are returned,
// not saved: the caller decides what to do with them.
func match(q core.Querier, minute models.DateData, now time.Time) (plan, error) {
	notifies, err := crud.FindNotifiesForMinute(q, minute)
	if err != nil {
		return plan{}, err
	}

	result := buildPlan(notifies, now)

	if err = crud.UpdateNotifyTimes(q, result.nextTimeIds, result.nextTimes); err != nil {
		return plan{}, err
	}
	if err = crud.DisableNotifies(q, result.disableIds); err != nil {
		return plan{}, err
	}

	return result, nil
}

// ProcessOperation matches one minute and writes the result in one
// transaction: either the operation is done and its messages are queued, or
// nothing has happened and the operation can be repeated.
func ProcessOperation(operation models.DateOperation) (int, error) {
	return processBatch([]models.DateOperation{operation})
}

// processBatch does the same for many minutes in ONE transaction: the minutes
// are handled in the order of ids (a notify "every N minutes" depends on the
// previous minute), and the commit is paid once. This is what makes a catch up
// after a long downtime fast.
func processBatch(operations []models.DateOperation) (int, error) {
	queued := 0

	err := core.InTx(func(tx *sql.Tx) error {
		queued = 0
		now := time.Now()
		ids := make([]int64, 0, len(operations))

		for _, operation := range operations {
			result, err := match(tx, operation.DateData, now)
			if err != nil {
				return err
			}

			if err = crud.InsertDeliveries(tx, result.deliveries, now); err != nil {
				return err
			}

			queued += len(result.deliveries)
			ids = append(ids, int64(operation.Id))
		}

		return crud.FinishDateOperations(tx, ids, crud.StatusDone, now)
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
		if len(operations) == 0 {
			return total, nil
		}

		queued, err := processBatch(operations)
		if err != nil {
			// find the bad minute: handle them one by one
			log.Printf("Batch of %d operations is fail, retry one by one: %v", len(operations), err)
			queued = 0

			for _, operation := range operations {
				one, err := processBatch([]models.DateOperation{operation})
				if err != nil {
					log.Printf("Operation %d is fail: %v", operation.Id, err)

					if err = date_operation_service.FinishOperation(operation.Id, true); err != nil {
						log.Printf("Can't save status of operation %d: %v", operation.Id, err)
					}
					continue
				}
				queued += one
			}
		}

		if queued > 0 || len(operations) > 1 {
			first, last := operations[0].DateData, operations[len(operations)-1].DateData
			log.Printf("Operations %d-%d (%s %s - %s %s): %d messages queued",
				operations[0].Id, operations[len(operations)-1].Id, first.Date, first.Time, last.Date, last.Time, queued)
		}
		total += queued

		if queued > 0 {
			delivery_service.Nudge() // deliver while the next minutes are matched
		}
	}
}

// Report is the result of a dry run.
type Report struct {
	Minutes  int
	Messages int
	ByType   map[string]int
	ByNotify []NotifyCount
}

type NotifyCount struct {
	Id       int
	Name     string
	Periodic string
	Count    int
}

// DryRun counts the messages which the minutes [from, to] would produce. It
// goes through the same code as the real processing, inside a transaction
// which is always rolled back: nothing is changed or sent.
func DryRun(from time.Time, to time.Time) (Report, error) {
	report := Report{ByType: map[string]int{}}
	counts := map[int]*NotifyCount{}

	tx, err := core.DB.Begin()
	if err != nil {
		return report, err
	}
	defer tx.Rollback()

	now := time.Now()
	for minute := from.UTC().Truncate(time.Minute); !minute.After(to); minute = minute.Add(time.Minute) {
		result, err := match(tx, date_operation_service.DateData(minute), now)
		if err != nil {
			return report, err
		}

		report.Minutes++
		for _, delivery := range result.deliveries {
			report.Messages++
			report.ByType[delivery.Type]++

			entry := counts[delivery.NotifyId]
			if entry == nil {
				entry = &NotifyCount{Id: delivery.NotifyId, Name: delivery.Title}
				counts[delivery.NotifyId] = entry
			}
			entry.Count++
		}
	}

	for _, entry := range counts {
		report.ByNotify = append(report.ByNotify, *entry)
	}
	sort.Slice(report.ByNotify, func(i, j int) bool { return report.ByNotify[i].Count > report.ByNotify[j].Count })

	return report, nil
}
