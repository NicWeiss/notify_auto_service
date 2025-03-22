package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
	"notifier/src/utils/query"
)

var emptyUser = models.User{}

func GetUserByPasswordAndEmail(password string, email string) (models.User, error) {
	sql := `select * from "user" where "password"=$1 and "email"=$2`
	rows, queryErr := core.Session.Query(sql, password, email)

	if queryErr != nil {
		return emptyUser, queryErr
	}

	res, processErr := query.QueryProcess(&emptyUser, rows, queryErr)
	records := emptyUser.MapRecords(res)

	if processErr != nil || len(records) == 0 {
		return emptyUser, processErr
	}

	return records[0], processErr
}

func GetUserBySession(sessionString string) (models.User, error) {
	sql := `select u.* from "session" s join "user" u on u.id = s.user_id where s.session=$1 limit 1`
	rows, queryErr := core.Session.Query(sql, sessionString)

	if queryErr != nil {
		return emptyUser, queryErr
	}

	res, processErr := query.QueryProcess(&emptyUser, rows, queryErr)
	records := emptyUser.MapRecords(res)

	if processErr != nil || len(records) == 0 {
		return emptyUser, processErr
	}

	return records[0], processErr
}
