package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
	"notifier/src/utils/query"
)

var emptySystem = models.System{}

func GetSystems() ([]models.System, error) {
	sql := `select * from "system"`
	rows, queryErr := core.Session.Query(sql)

	res, processErr := query.QueryProcess(&emptySystem, rows, queryErr)
	return emptySystem.MapRecords(res), processErr
}
