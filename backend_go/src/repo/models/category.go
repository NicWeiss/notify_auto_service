package models

import (
	"database/sql"

	"notifier/src/utils"
)

type Category struct {
	Id        int    `json:"id"`
	IsDeleted bool   `json:"is_deleted"`
	Name      string `json:"name"`
	UserId    int    `json:"user_id"`
	IsHidden  bool   `json:"is_hidden"`
}

func (u Category) GetFromRows(rows *sql.Rows) ([]Category, error) {
	var records []Category

	for rows.Next() {
		var model = Category{}
		err := utils.MapRowToModel(rows, &model)
		if err != nil {
			return nil, err
		}
		records = append(records, model)
	}

	return records, nil
}
