package models

import (
	"time"
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

func (n *Notify) GetEmptyItem() (any, any) {
	nt := Notify{}
	return nt, &nt
}

func (n *Notify) MapRecords(prepared []interface{}) []Notify {
	var result = make([]Notify, 0)
	for _, element := range prepared {
		result = append(result, element.(Notify))
	}

	return result
}
