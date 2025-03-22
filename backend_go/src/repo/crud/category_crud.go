package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
	"notifier/src/utils/query"
)

var emptyCategory = models.Category{}

func GetCategoriesByUserID(userId int) ([]models.Category, error) {
	sql := `select * from "category" where "user_id"=$1`
	rows, queryErr := core.Session.Query(sql, userId)

	res, processErr := query.QueryProcess(&emptyCategory, rows, queryErr)
	return emptyCategory.MapRecords(res), processErr
}
