package models

import (
	"database/sql"
	"time"

	"notifier/src/utils"
)

type User struct {
	Id          int       `json:"id"`
	IsDeleted   bool      `json:"is_deleted"`
	Name        string    `json:"name"`
	Email       string    `json:"email"`
	Password    string    `json:"password"`
	RegistredAt time.Time `json:"registred_at"`
}

func (u User) GetFromRows(rows *sql.Rows) ([]User, error) {
	var records []User

	for rows.Next() {
		var model = User{}
		err := utils.MapRowToModel(rows, &model)
		if err != nil {
			return nil, err
		}
		records = append(records, model)
	}

	return records, nil
}
