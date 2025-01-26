package models

import (
	"database/sql"
	"time"

	"notifier/src/utils"
)

type Session struct {
	Id        int         `json:"id"`
	IsDeleted bool        `json:"is_deleted"`
	Session   string      `json:"session"`
	ExpireAt  time.Time   `json:"expire_at"`
	UserId    int         `json:"user_id"`
	Client    interface{} `json:"client"`
	Location  interface{} `json:"location"`
}

func (m Session) GetFromRows(rows *sql.Rows) ([]Session, error) {
	var records []Session

	for rows.Next() {
		var model = Session{}
		utils.MapRowToModel(rows, &model)
		records = append(records, model)
	}

	return records, nil
}
