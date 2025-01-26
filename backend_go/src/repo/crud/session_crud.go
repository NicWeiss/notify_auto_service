package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
	"notifier/src/repo/schemas"
)

func GetLatestSessionByUserId(user_id int) (models.Session, error) {
	var empty_session = models.Session{}
	query := `select * from "session" where "user_id"=$1 order by id desc limit 1`
	rows, err := core.Session.Query(query, user_id)

	if err != nil {
		return empty_session, err
	}

	var records, errors = models.Session{}.GetFromRows(rows)

	if errors != nil || len(records) == 0 {
		return empty_session, err
	}

	return records[0], nil
}

func CreateSession(create_scheme schemas.SessionCreate) (models.Session, error) {
	var empty_session = models.Session{}
	query := `insert into "session" ("session", "expire_at", "user_id") VALUES ($1, $2, $3)`
	_, err := core.Session.Exec(
		query,
		create_scheme.Session,
		create_scheme.ExpireAt,
		create_scheme.UserId,
	)

	if err != nil {
		return empty_session, err
	}

	return GetLatestSessionByUserId(create_scheme.UserId)
}
