package crud

import (
	"errors"
	_ "errors"
	"notifier/src/core"
	"notifier/src/repo/models"
)

var emptyUser = models.User{}

func GetUserByPasswordAndEmail(password string, email string) (models.User, bool) {
	query := `select * from "user" where "password"=$1 and "email"=$2`
	rows, queryErr := core.Session.Query(query, password, email)

	if queryErr != nil {
		return emptyUser, true
	}

	defer rows.Close()
	var records, err = models.User{}.GetFromRows(rows)

	if err != nil || len(records) == 0 {
		return emptyUser, true
	}

	return records[0], false
}

func GetUserBySession(sessionString string) (models.User, error) {
	query := `select u.* from "session" s join "user" u on u.id = s.user_id where s.session=$1 limit 1`
	rows, queryErr := core.Session.Query(query, sessionString)

	if queryErr != nil {
		return emptyUser, queryErr
	}

	defer rows.Close()
	var records, err = emptyUser.GetFromRows(rows)

	if err != nil {
		return emptyUser, err
	}
	if len(records) == 0 {
		return emptyUser, errors.New("user not found or session is not valid")
	}

	return records[0], err
}
