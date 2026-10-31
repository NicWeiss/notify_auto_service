package models

import (
	"encoding/json"
	"time"
)

// All timestamps are stored in UTC.

type User struct {
	Id          int        `json:"id"`
	IsDeleted   bool       `json:"is_deleted"`
	Name        string     `json:"name"`
	Email       string     `json:"email"`
	Password    string     `json:"-"`
	RegistredAt *time.Time `json:"registred_at"`
}

type System struct {
	Id         int    `json:"id"`
	IsDeleted  bool   `json:"is_deleted"`
	IsDisabled bool   `json:"is_disabled"`
	IsSystem   bool   `json:"is_system"`
	Name       string `json:"name"`
	Help       string `json:"help"`
	Type       string `json:"type"`
}

type Acceptor struct {
	Id         int    `json:"id"`
	IsDeleted  bool   `json:"is_deleted"`
	IsDisabled bool   `json:"is_disabled"`
	IsSystem   bool   `json:"is_system"`
	Name       string `json:"name"`
	UserId     int    `json:"user_id"`
	SystemId   int    `json:"system_id"`
	Account    string `json:"account"`

	// filled only by queries which join the system table (sending)
	SystemType string `json:"-"`
}

type Category struct {
	Id        int    `json:"id"`
	IsDeleted bool   `json:"is_deleted"`
	Name      string `json:"name"`
	UserId    int    `json:"user_id"`
	IsHidden  bool   `json:"is_hidden"`
}

type Notify struct {
	Id              int        `json:"id"`
	IsDeleted       bool       `json:"is_deleted"`
	Name            string     `json:"name"`
	UserId          int        `json:"user_id"`
	Text            *string    `json:"text"`
	Periodic        string     `json:"periodic"`
	DayOfWeek       *int       `json:"day_of_week"`
	Date            *string    `json:"date"`
	Time            string     `json:"time"`
	RepeateInterval *int       `json:"repeate_interval"`
	IsDisabled      bool       `json:"is_disabled"`
	CategoryId      *int       `json:"category_id"`
	IsAutodisable   bool       `json:"is_autodisable"`
	AutodisableAt   *time.Time `json:"autodisable_at"`
	Acceptors       []Acceptor `json:"acceptors"`
}

type Session struct {
	Id        int             `json:"id"`
	IsDeleted bool            `json:"is_deleted"`
	Session   string          `json:"session"`
	ExpireAt  time.Time       `json:"expire_at"`
	UserId    int             `json:"user_id"`
	Client    json.RawMessage `json:"client" swaggertype:"object"`
	Location  json.RawMessage `json:"location" swaggertype:"object"`
}

type RegCode struct {
	Id       int
	Code     string
	Email    string
	ExpireAt time.Time
}

type DateOperation struct {
	Id       int
	DateData DateData
}

// DateData describes one minute which has to be checked for notifies. The
// json keys are the same as the python worker wrote to date_operation.
type DateData struct {
	FirstDay  int    `json:"first_day"`
	LastDay   int    `json:"last_day"`
	Day       int    `json:"day"`
	DayOfWeek int    `json:"day_of_week"`
	Month     int    `json:"month"`
	Time      string `json:"time"`
	Date      string `json:"date"`
}

// Delivery is one message for one recipient in the outbox.
type Delivery struct {
	Id       int64
	NotifyId int
	UserId   int
	Type     string // system type: email, tg, push
	Account  string
	Title    string
	Body     string
	Attempts int
}
