package schemas

import "time"

type SessionCreate struct {
	Session  string    `json:"session"`
	ExpireAt time.Time `json:"expire_at"`
	UserId   int       `json:"user_id"`
}
