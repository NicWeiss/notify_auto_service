package crud

import (
	"encoding/json"
	"time"

	"notifier/src/core"
	"notifier/src/repo/models"
)

const sessionColumns = `id, coalesce(is_deleted, false), coalesce(session, ''), expire_at, user_id, client, location`

func scanSession(row scanner) (models.Session, error) {
	var s models.Session
	// database/sql can't scan NULL into json.RawMessage directly
	var client, location []byte

	err := row.Scan(&s.Id, &s.IsDeleted, &s.Session, &s.ExpireAt, &s.UserId, &client, &location)
	s.Client, s.Location = client, location
	return s, err
}

func CreateSession(
	q core.Querier,
	token string,
	userId int,
	client json.RawMessage,
	location json.RawMessage,
	expireAt time.Time,
) (models.Session, error) {
	// json is passed as string: lib/pq would send []byte as bytea
	return scanSession(q.QueryRow(
		`insert into session (session, user_id, client, location, expire_at, is_deleted)
		 values ($1, $2, $3, $4, $5, false) returning `+sessionColumns,
		token, userId, nullableJson(client), nullableJson(location), expireAt.UTC(),
	))
}

func GetSessionsByUserId(q core.Querier, userId int) ([]models.Session, error) {
	rows, err := q.Query(
		`select `+sessionColumns+` from session where user_id=$1 and is_deleted is not true order by id`,
		userId,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	sessions := make([]models.Session, 0)
	for rows.Next() {
		s, err := scanSession(rows)
		if err != nil {
			return nil, err
		}
		sessions = append(sessions, s)
	}

	return sessions, rows.Err()
}

// GetUserBySession returns the owner of a living session.
func GetUserBySession(q core.Querier, token string, now time.Time) (models.User, error) {
	return scanUser(q.QueryRow(
		`select u.id, coalesce(u.is_deleted, false), coalesce(u.name, ''), coalesce(u.email, ''), coalesce(u.password, ''), u.registred_at
		 from session s join "user" u on u.id = s.user_id
		 where s.session=$1 and s.expire_at > $2 and s.is_deleted is not true and u.is_deleted is not true
		 limit 1`,
		token, now.UTC(),
	))
}

func DeleteSession(q core.Querier, id int, userId int) (bool, error) {
	result, err := q.Exec(`delete from session where id=$1 and user_id=$2`, id, userId)
	if err != nil {
		return false, err
	}

	affected, err := result.RowsAffected()
	return affected > 0, err
}

func DeleteSessionsByUserId(q core.Querier, userId int) error {
	_, err := q.Exec(`delete from session where user_id=$1`, userId)
	return err
}

func ClearExpiredSessions(q core.Querier, now time.Time) error {
	_, err := q.Exec(`delete from session where expire_at < $1`, now.UTC())
	return err
}

func nullableJson(raw json.RawMessage) any {
	if len(raw) == 0 {
		return nil
	}

	return string(raw)
}
