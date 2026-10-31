package schemas

import (
	"fmt"
	"strconv"
	"strings"

	"notifier/src/repo/models"
)

// FlexInt is a number which may also come as a string: Ember Data sends the ids
// of the records as strings ("id": "2"), and the python backend (pydantic)
// converted them silently.
type FlexInt int

func (f *FlexInt) UnmarshalJSON(raw []byte) error {
	text := strings.Trim(strings.TrimSpace(string(raw)), `"`)
	if text == "null" {
		return nil
	}

	number, err := strconv.Atoi(text)
	if err != nil {
		return fmt.Errorf("%q is not an integer", text)
	}

	*f = FlexInt(number)
	return nil
}

// Int returns the value of an optional field.
func (f *FlexInt) Int() *int {
	if f == nil {
		return nil
	}

	value := int(*f)
	return &value
}

// --- responses (used for the swagger docs) ---

type EmberMeta struct {
	TotalPages int `json:"total_pages"`
}

type EmberCategories struct {
	Meta     EmberMeta         `json:"meta"`
	Category []models.Category `json:"category"`
}

type EmberCategory struct {
	Meta     EmberMeta       `json:"meta"`
	Category models.Category `json:"category"`
}

type EmberNotifies struct {
	Meta   EmberMeta       `json:"meta"`
	Notify []models.Notify `json:"notify"`
}

type EmberNotify struct {
	Meta   EmberMeta     `json:"meta"`
	Notify models.Notify `json:"notify"`
}

type EmberSessions struct {
	Meta    EmberMeta        `json:"meta"`
	Session []models.Session `json:"session"`
}

type EmberUser struct {
	Meta EmberMeta   `json:"meta"`
	User models.User `json:"user"`
}

type EmberSystems struct {
	Meta   EmberMeta       `json:"meta"`
	System []models.System `json:"system"`
}

type EmberSystem struct {
	Meta   EmberMeta     `json:"meta"`
	System models.System `json:"system"`
}

type EmberAcceptors struct {
	Meta     EmberMeta         `json:"meta"`
	Acceptor []models.Acceptor `json:"acceptor"`
}

type EmberAcceptor struct {
	Meta     EmberMeta       `json:"meta"`
	Acceptor models.Acceptor `json:"acceptor"`
}

type SessionResponse struct {
	Session string `json:"session"`
}

type DataResponse struct {
	Data map[string]any `json:"data"`
}

// --- auth ---

type LoginParams struct {
	Email    string             `json:"email" binding:"required"`
	Password string             `json:"password" binding:"required"`
	Client   *models.ClientInfo `json:"client"`
}

type SignUpParams struct {
	Name     string             `json:"name" binding:"required"`
	Email    string             `json:"email" binding:"required"`
	Password string             `json:"password" binding:"required"`
	Code     string             `json:"code" binding:"required"`
	Client   *models.ClientInfo `json:"client"`
}

type CodeRequestParams struct {
	Email string `json:"email" binding:"required"`
}

type RestoreParams struct {
	Email string `json:"email" binding:"required"`
}

type RestoreVerifyCodeParams struct {
	Code string `json:"code" binding:"required"`
}

type RestorePasswordParams struct {
	Code     string `json:"code" binding:"required"`
	Password string `json:"password" binding:"required"`
}

// --- category ---

type CategoryFields struct {
	Name     string `json:"name" binding:"required"`
	IsHidden bool   `json:"is_hidden"`
}

type CategoryParams struct {
	Category CategoryFields `json:"category" binding:"required"`
}

// --- acceptor ---

type AcceptorCreateFields struct {
	Name       string  `json:"name" binding:"required"`
	Account    string  `json:"account"`
	SystemId   FlexInt `json:"system_id" binding:"required"`
	IsSystem   bool    `json:"is_system"`
	IsDisabled bool    `json:"is_disabled"`
}

type AcceptorCreateParams struct {
	Acceptor AcceptorCreateFields `json:"acceptor" binding:"required"`
}

type AcceptorUpdateFields struct {
	Name       *string  `json:"name"`
	Account    *string  `json:"account"`
	SystemId   *FlexInt `json:"system_id"`
	IsDisabled *bool    `json:"is_disabled"`
}

type AcceptorUpdateParams struct {
	Acceptor AcceptorUpdateFields `json:"acceptor" binding:"required"`
}

type PushTokenParams struct {
	Token string `json:"token" binding:"required"`
}

// --- notify ---

type AcceptorRef struct {
	Id FlexInt `json:"id"`
}

type NotifyFields struct {
	Acceptors       *[]AcceptorRef `json:"acceptors"`
	Name            *string        `json:"name"`
	Text            *string        `json:"text"`
	Periodic        *string        `json:"periodic"`
	DayOfWeek       *FlexInt       `json:"day_of_week"`
	Date            *string        `json:"date"`
	Time            *string        `json:"time"`
	RepeateInterval *FlexInt       `json:"repeate_interval"`
	IsDisabled      *bool          `json:"is_disabled"`
	CategoryId      *FlexInt       `json:"category_id"`
	IsAutodisable   *bool          `json:"is_autodisable"`
	AutodisableAt   *string        `json:"autodisable_at"`
}

type NotifyParams struct {
	Notify NotifyFields `json:"notify" binding:"required"`
}

type NotifyCategoryParams struct {
	CategoryId FlexInt `json:"category_id" binding:"required"`
}

// --- user ---

type UserUpdateFields struct {
	Name  *string `json:"name"`
	Email *string `json:"email"`
}

type UserUpdateParams struct {
	User UserUpdateFields `json:"user" binding:"required"`
}

type ChangePasswordParams struct {
	CurrentPassword string `json:"current_password" binding:"required"`
	NewPassword     string `json:"new_password" binding:"required"`
}
