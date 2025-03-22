package models

import (
	"time"
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

func (n *Session) GetEmptyItem() (any, any) {
	nt := Session{}
	return nt, &nt
}

func (n *Session) MapRecords(prepared []interface{}) []Session {
	var result = make([]Session, 0)
	for _, element := range prepared {
		result = append(result, element.(Session))
	}

	return result
}
