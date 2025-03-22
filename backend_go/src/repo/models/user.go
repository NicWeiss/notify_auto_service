package models

import (
	"time"
)

type User struct {
	Id          int       `json:"id"`
	IsDeleted   bool      `json:"is_deleted"`
	Name        string    `json:"name"`
	Email       string    `json:"email"`
	Password    string    `json:"password"`
	RegistredAt time.Time `json:"registred_at"`
}

func (n *User) GetEmptyItem() (any, any) {
	nt := User{}
	return nt, &nt
}

func (n *User) MapRecords(prepared []interface{}) []User {
	var result = make([]User, 0)
	for _, element := range prepared {
		result = append(result, element.(User))
	}

	return result
}
