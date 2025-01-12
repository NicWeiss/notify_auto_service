package models

import (
	"database/sql"
	"time"

	"notifier/utils"
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
		utils.MapRowToModel(rows, &model)
		records = append(records, model)
	}

	return records, nil
}
