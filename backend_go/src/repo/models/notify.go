package models

import (
	"database/sql"
	"time"

	"notifier/src/utils"
)

type Notify struct {
	Id              int         `json:"id"`
	IsDeleted       bool        `json:"is_deleted"`
	Name            string      `json:"name"`
	UserId          int         `json:"user_id"`
	Text            string      `json:"text"`
	Periodic        string      `json:"periodic"`
	DayOfWeek       int         `json:"day_of_week"`
	Date            string      `json:"date"`
	Time            string      `json:"time"`
	RepeateInterval int         `json:"repeate_interval"`
	IsDisabled      bool        `json:"is_disabled"`
	CategoryId      int         `json:"category_id"`
	IsAutodisable   bool        `json:"is_autodisable"`
	AutodisableAt   time.Time   `json:"autodisable_at"`
	Acceptors       interface{} `json:"acceptors"`
}

func (u Notify) GetFromRows(rows *sql.Rows) ([]Notify, error) {
	var records []Notify

	for rows.Next() {
		var model = Notify{}
		err := utils.MapRowToModel(rows, &model)
		if err != nil {
			return nil, err
		}
		records = append(records, model)
	}

	return records, nil
}
