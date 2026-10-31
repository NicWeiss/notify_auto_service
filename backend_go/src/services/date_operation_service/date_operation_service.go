package date_operation_service

import (
	"database/sql"
	"log"
	"time"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

const (
	timeWatcherLockId = 4242

	stuckAfter         = 2 * time.Minute
	doneOperationsKeep = time.Hour
)

// CreateNewOperations creates an operation for every minute which has come
// since the previous call. It is safe to run on several instances.
func CreateNewOperations(now time.Time) (int, error) {
	now = now.UTC().Truncate(time.Minute)

	created := 0

	err := core.InTx(func(tx *sql.Tx) error {
		if _, err := tx.Exec(`select pg_advisory_xact_lock($1)`, timeWatcherLockId); err != nil {
			return err
		}

		lastDate := now.Add(-time.Minute)
		last, err := crud.GetLastOperationTime(tx)
		if err == nil {
			lastDate = last.UTC().Truncate(time.Minute)
		} else if err != sql.ErrNoRows {
			return err
		}

		if lastDate.After(now) {
			return nil
		}

		minutes := int(now.Sub(lastDate) / time.Minute)
		if minutes > core.Cfg.CatchUpMinutes {
			log.Printf("Service was down for %d minutes, only the last %d are checked", minutes, core.Cfg.CatchUpMinutes)
			minutes = core.Cfg.CatchUpMinutes
		}

		for back := minutes - 1; back >= 0; back-- {
			date := now.Add(-time.Duration(back) * time.Minute)
			if err = crud.CreateDateOperation(tx, DateData(date), now); err != nil {
				return err
			}
			created++
		}

		return nil
	})

	return created, err
}

// DateData describes a minute. Day of week: monday is 1, sunday is 7.
func DateData(date time.Time) models.DateData {
	dayOfWeek := int(date.Weekday())
	if dayOfWeek == 0 {
		dayOfWeek = 7
	}

	return models.DateData{
		FirstDay:  1,
		LastDay:   time.Date(date.Year(), date.Month()+1, 0, 0, 0, 0, 0, time.UTC).Day(),
		Day:       date.Day(),
		DayOfWeek: dayOfWeek,
		Month:     int(date.Month()),
		Time:      date.Format("15:04"),
		Date:      date.Format("2006-01-02"),
	}
}

func ClaimOperations(limit int) ([]models.DateOperation, error) {
	return crud.ClaimDateOperations(core.DB, limit, time.Now())
}

func FinishOperation(id int, failed bool) error {
	status := crud.StatusDone
	if failed {
		status = crud.StatusError
	}

	return crud.FinishDateOperation(core.DB, id, status, time.Now())
}

func ResetStuckOperations() error {
	count, err := crud.ResetStuckDateOperations(core.DB, time.Now().Add(-stuckAfter))
	if err == nil && count > 0 {
		log.Printf("%d stuck operations dropped to new", count)
	}

	return err
}

func ClearDoneOperations() error {
	return crud.ClearDoneDateOperations(core.DB, time.Now().Add(-doneOperationsKeep))
}
