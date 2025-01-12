package crud

import (
	"notifier/core"
	"notifier/repo/models"
)

func GetUserByPasswordAndEmail(password string, email string) (models.User, bool) {
	query := `select * from "user" where "password"=$1 and "email"=$2`
	rows, err := core.Session.Query(query, password, email)
	var empty_user = models.User{}

	if err != nil {
		return empty_user, true
	}

	defer rows.Close()
	var records, errors = models.User{}.GetFromRows(rows)

	if errors != nil || len(records) == 0 {
		return empty_user, true
	}

	return records[0], false
}
