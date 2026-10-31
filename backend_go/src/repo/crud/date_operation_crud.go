package crud

import (
	"encoding/json"
	"sort"
	"time"

	"github.com/lib/pq"

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

// CreateDateOperations inserts many operations by few statements.
func CreateDateOperations(q core.Querier, data []models.DateData, createAt time.Time) error {
	const chunk = 5000

	for start := 0; start < len(data); start += chunk {
		end := min(start+chunk, len(data))

		raws := make([]string, 0, end-start)
		for _, item := range data[start:end] {
			raw, err := json.Marshal(item)
			if err != nil {
				return err
			}
			raws = append(raws, string(raw))
		}

		_, err := q.Exec(
			`insert into date_operation (date_data, create_at, status, is_deleted)
			 select v.data::jsonb, $2, $3, false from unnest($1::text[]) as v(data) order by v.data::jsonb->>'date', v.data::jsonb->>'time'`,
			pq.Array(raws), createAt.UTC(), StatusNew,
		)
		if err != nil {
			return err
		}
	}

	return nil
}

// FinishDateOperations sets one status for many operations.
func FinishDateOperations(q core.Querier, ids []int64, status string, now time.Time) error {
	if len(ids) == 0 {
		return nil
	}

	_, err := q.Exec(`update date_operation set status=$2, complete_at=$3 where id = any($1)`, pq.Array(ids), status, now.UTC())
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

	// RETURNING gives the rows in any order, but the minutes have to be handled
	// chronologically: a notify "every N minutes" depends on the previous minute
	sort.Slice(operations, func(i, j int) bool { return operations[i].Id < operations[j].Id })

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
