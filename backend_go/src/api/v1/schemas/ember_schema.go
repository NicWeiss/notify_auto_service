package schemas

import "notifier/src/repo/models"

type EmberMeta struct {
	TotalPage int `json:"total_pages"`
}

type EmberCategories struct {
	Meta     EmberMeta         `json:"meta"`
	Category []models.Category `json:"category"`
}

type EmberNotifies struct {
	Meta   EmberMeta       `json:"meta"`
	Notify []models.Notify `json:"notify"`
}
