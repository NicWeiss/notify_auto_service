package crud

import (
	"database/sql"
	"notifier/src/core"
	"notifier/src/repo/models"
)

var emptyModel = models.Notify{}
var emptyModels = make([]models.Notify, 0)

func processQuery(rows *sql.Rows, queryErr error) ([]models.Notify, error) {
	if queryErr != nil {
		return emptyModels, queryErr
	}

	defer rows.Close()
	var records, errors = emptyModel.GetFromRows(rows)

	if errors != nil {
		return emptyModels, errors
	}

	if records == nil {
		return emptyModels, nil
	}

	return records, errors
}

func GetNotifiesByUserId(userId int) ([]models.Notify, error) {
	query := `select * from "notify" where "user_id"=$1`
	rows, queryErr := core.Session.Query(query, userId)

	return processQuery(rows, queryErr)
}

func GetNotifiesByUserIdAndCategoryId(userId int, categoryId int) ([]models.Notify, error) {
	query := `select * from "notify" where "user_id"=$1`

	if categoryId == 0 {
		query = query + ` and ("category_id" is null or "category_id" = $2)`
	} else {
		query = query + ` and "category_id"=$2`
	}

	rows, queryErr := core.Session.Query(query, userId, categoryId)

	return processQuery(rows, queryErr)
}
