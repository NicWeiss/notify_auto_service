package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
	"notifier/src/utils/query"
)

var emptyAcceptor = models.Acceptor{}

func GetAcceptorsByUserID(userId int) ([]models.Acceptor, error) {
	sql := `select * from "acceptor" where "user_id"=$1`
	rows, queryErr := core.Session.Query(sql, userId)

	res, processErr := query.QueryProcess(&emptyAcceptor, rows, queryErr)
	return emptyAcceptor.MapRecords(res), processErr
}
