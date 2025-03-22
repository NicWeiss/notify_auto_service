package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
	"notifier/src/utils/query"
)

var notify = models.Notify{}

func GetNotifiesByUserId(userId int) ([]models.Notify, error) {
	sql := `select * from "notify" where "user_id"=$1`
	rows, queryErr := core.Session.Query(sql, userId)

	res, processErr := query.QueryProcess(&notify, rows, queryErr)
	return notify.MapRecords(res), processErr
}

func GetNotifiesByUserIdAndCategoryId(userId int, categoryId int) ([]models.Notify, error) {
	sql := `select * from "notify" where "user_id"=$1`

	if categoryId == 0 {
		sql = sql + ` and ("category_id" is null or "category_id" = $2)`
	} else {
		sql = sql + ` and "category_id"=$2`
	}

	rows, queryErr := core.Session.Query(sql, userId, categoryId)

	res, processErr := query.QueryProcess(&notify, rows, queryErr)
	return notify.MapRecords(res), processErr
}
