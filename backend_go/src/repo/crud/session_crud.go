package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
	"notifier/src/repo/schemas"
	"notifier/src/utils/query"
)

var session = models.Session{}

func GetLatestSessionByUserId(user_id int) (models.Session, error) {
	sql := `select * from "session" where "user_id"=$1 order by id desc limit 1`
	rows, queryErr := core.Session.Query(sql, user_id)

	res, processErr := query.QueryProcess(&session, rows, queryErr)
	records := session.MapRecords(res)

	if processErr != nil || len(records) == 0 {
		return session, processErr
	}

	return records[0], processErr
}

func CreateSession(create_scheme schemas.SessionCreate) (models.Session, error) {
	sql := `insert into "session" ("session", "expire_at", "user_id") VALUES ($1, $2, $3)`
	_, err := core.Session.Exec(
		sql,
		create_scheme.Session,
		create_scheme.ExpireAt,
		create_scheme.UserId,
	)

	if err != nil {
		return session, err
	}

	return GetLatestSessionByUserId(create_scheme.UserId)
}
