package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
)

var emptyCategory = models.Category{}
var emptyCategories = make([]models.Category, 0)

func GetCategoriesByUserID(userId int) ([]models.Category, error) {
	query := `select * from "category" where "user_id"=$1`
	rows, query_err := core.Session.Query(query, userId)

	if query_err != nil {
		return emptyCategories, query_err
	}

	defer rows.Close()
	var records, errors = emptyCategory.GetFromRows(rows)

	if errors != nil || len(records) == 0 {
		return emptyCategories, errors
	}

	return records, errors
}
