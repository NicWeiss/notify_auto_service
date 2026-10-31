package crud

import (
	"time"

	"github.com/lib/pq"

	"notifier/src/core"
	"notifier/src/repo/models"
)

const (
	DeliveryNew     = "new"
	DeliveryProcess = "process"
	DeliveryDone    = "done"
	DeliveryFailed  = "failed"

	insertChunk = 2000
)

// InsertDeliveries puts messages into the queue. They are inserted in chunks,
// one statement per chunk.
func InsertDeliveries(q core.Querier, deliveries []models.Delivery, now time.Time) error {
	for start := 0; start < len(deliveries); start += insertChunk {
		end := min(start+insertChunk, len(deliveries))
		chunk := deliveries[start:end]

		notifyIds := make([]int64, len(chunk))
		userIds := make([]int64, len(chunk))
		types := make([]string, len(chunk))
		accounts := make([]string, len(chunk))
		titles := make([]string, len(chunk))
		bodies := make([]string, len(chunk))

		for i, d := range chunk {
			notifyIds[i], userIds[i] = int64(d.NotifyId), int64(d.UserId)
			types[i], accounts[i], titles[i], bodies[i] = d.Type, d.Account, d.Title, d.Body
		}

		_, err := q.Exec(
			`insert into delivery (notify_id, user_id, system_type, account, title, body, status, attempts, next_attempt_at, created_at)
			 select v.notify_id, v.user_id, v.system_type, v.account, v.title, v.body, $7, 0, $8, $8
			 from unnest($1::int[], $2::int[], $3::text[], $4::text[], $5::text[], $6::text[])
			   as v(notify_id, user_id, system_type, account, title, body)`,
			pq.Array(notifyIds), pq.Array(userIds), pq.Array(types), pq.Array(accounts),
			pq.Array(titles), pq.Array(bodies), DeliveryNew, now.UTC(),
		)
		if err != nil {
			return err
		}
	}

	return nil
}

// ClaimDeliveries atomically takes up to limit messages of one channel which
// are due. Several dispatchers can work in parallel: rows locked by another
// one are skipped.
func ClaimDeliveries(q core.Querier, systemType string, limit int, now time.Time) ([]models.Delivery, error) {
	rows, err := q.Query(
		`update delivery set status=$1, locked_at=$2, attempts=attempts+1
		 where id in (
		   select id from delivery
		   where status=$3 and system_type=$4 and next_attempt_at <= $2
		   order by next_attempt_at, id
		   for update skip locked limit $5
		 )
		 returning id, coalesce(notify_id, 0), coalesce(user_id, 0), system_type, account, title, body, attempts`,
		DeliveryProcess, now.UTC(), DeliveryNew, systemType, limit,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	deliveries := make([]models.Delivery, 0, limit)
	for rows.Next() {
		var d models.Delivery
		if err = rows.Scan(&d.Id, &d.NotifyId, &d.UserId, &d.Type, &d.Account, &d.Title, &d.Body, &d.Attempts); err != nil {
			return nil, err
		}
		deliveries = append(deliveries, d)
	}

	return deliveries, rows.Err()
}

func MarkDeliveriesDone(q core.Querier, ids []int64, now time.Time) error {
	if len(ids) == 0 {
		return nil
	}

	_, err := q.Exec(
		`update delivery set status=$1, sent_at=$2, locked_at=null, last_error=null where id = any($3)`,
		DeliveryDone, now.UTC(), pq.Array(ids),
	)

	return err
}

// RetryDeliveries returns messages to the queue, every one with its own delay.
func RetryDeliveries(q core.Querier, ids []int64, delaysSeconds []int64, errors []string, now time.Time) error {
	if len(ids) == 0 {
		return nil
	}

	_, err := q.Exec(
		`update delivery d set status=$1, locked_at=null, last_error=v.error,
		   next_attempt_at = $2::timestamp + v.delay * interval '1 second'
		 from unnest($3::bigint[], $4::bigint[], $5::text[]) as v(id, delay, error)
		 where d.id = v.id`,
		DeliveryNew, now.UTC(), pq.Array(ids), pq.Array(delaysSeconds), pq.Array(errors),
	)

	return err
}

func FailDeliveries(q core.Querier, ids []int64, errors []string, now time.Time) error {
	if len(ids) == 0 {
		return nil
	}

	_, err := q.Exec(
		`update delivery d set status=$1, locked_at=null, sent_at=$2, last_error=v.error
		 from unnest($3::bigint[], $4::text[]) as v(id, error)
		 where d.id = v.id`,
		DeliveryFailed, now.UTC(), pq.Array(ids), pq.Array(errors),
	)

	return err
}

// ResetStuckDeliveries returns messages of a dead dispatcher to the queue.
func ResetStuckDeliveries(q core.Querier, lockedBefore time.Time) (int64, error) {
	result, err := q.Exec(
		`update delivery set status=$1, locked_at=null where status=$2 and locked_at < $3`,
		DeliveryNew, DeliveryProcess, lockedBefore.UTC(),
	)
	if err != nil {
		return 0, err
	}

	return result.RowsAffected()
}

func ClearDeliveries(q core.Querier, doneBefore time.Time, failedBefore time.Time) error {
	_, err := q.Exec(
		`delete from delivery where (status=$1 and sent_at < $2) or (status=$3 and sent_at < $4)`,
		DeliveryDone, doneBefore.UTC(), DeliveryFailed, failedBefore.UTC(),
	)

	return err
}
