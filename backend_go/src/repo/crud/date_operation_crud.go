package crud

import (
	"encoding/json"
	"time"

	"notifier/src/core"
	"notifier/src/repo/models"
)

const (
	StatusNew     = "new"
	StatusProcess = "process"
	StatusDone    = "done"
	StatusError   = "error"
)

// GetLastOperationTime returns create_at of the latest not failed operation.
func GetLastOperationTime(q core.Querier) (time.Time, error) {
	var createAt time.Time
	err := q.QueryRow(
		`select create_at from date_operation where status != $1 and create_at is not null order by id desc limit 1`,
		StatusError,
	).Scan(&createAt)

	return createAt, err
}

func CreateDateOperation(q core.Querier, data models.DateData, createAt time.Time) error {
	raw, err := json.Marshal(data)
	if err != nil {
		return err
	}

	_, err = q.Exec(
		`insert into date_operation (date_data, create_at, status, is_deleted) values ($1, $2, $3, false)`,
		string(raw), createAt.UTC(), StatusNew,
	)

	return err
}

// ClaimDateOperations atomically moves up to limit new operations to the
// process status, so several workers never take the same operation.
func ClaimDateOperations(q core.Querier, limit int, now time.Time) ([]models.DateOperation, error) {
	rows, err := q.Query(
		`update date_operation set status=$1, start_process_at=$2
		 where id in (
		   select id from date_operation where status=$3 order by id for update skip locked limit $4
		 )
		 returning id, date_data`,
		StatusProcess, now.UTC(), StatusNew, limit,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	operations := make([]models.DateOperation, 0)
	for rows.Next() {
		var operation models.DateOperation
		var raw []byte

		if err = rows.Scan(&operation.Id, &raw); err != nil {
			return nil, err
		}

		if err = json.Unmarshal(raw, &operation.DateData); err != nil {
			// a broken record must not be taken again and again
			q.Exec(`update date_operation set status=$2, complete_at=$3 where id=$1`, operation.Id, StatusError, now.UTC())
			continue
		}

		operations = append(operations, operation)
	}

	return operations, rows.Err()
}

func FinishDateOperation(q core.Querier, id int, status string, now time.Time) error {
	_, err := q.Exec(`update date_operation set status=$2, complete_at=$3 where id=$1`, id, status, now.UTC())
	return err
}

// ResetStuckDateOperations returns operations, which are processed for too long
// (the worker has died), back to the queue.
func ResetStuckDateOperations(q core.Querier, startedBefore time.Time) (int64, error) {
	result, err := q.Exec(
		`update date_operation set status=$1, start_process_at=null
		 where status=$2 and (start_process_at is null or start_process_at < $3)`,
		StatusNew, StatusProcess, startedBefore.UTC(),
	)
	if err != nil {
		return 0, err
	}

	return result.RowsAffected()
}

// ClearDoneDateOperations removes old finished operations. The latest one is
// always kept: the time watcher uses it to know which minutes are already created.
func ClearDoneDateOperations(q core.Querier, completedBefore time.Time) error {
	_, err := q.Exec(
		`delete from date_operation
		 where status=$1 and complete_at < $2
		   and id <> coalesce((select max(id) from date_operation where status != $3), 0)`,
		StatusDone, completedBefore.UTC(), StatusError,
	)

	return err
}
