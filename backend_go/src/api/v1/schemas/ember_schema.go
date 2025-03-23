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

type EmberSessions struct {
	Meta    EmberMeta        `json:"meta"`
	Session []models.Session `json:"session"`
}

type EmberUser struct {
	Meta    EmberMeta   `json:"meta"`
	Session models.User `json:"user"`
}

type EmberSystems struct {
	Meta    EmberMeta       `json:"meta"`
	Session []models.System `json:"system"`
}

type EmberAcceptors struct {
	Meta    EmberMeta         `json:"meta"`
	Session []models.Acceptor `json:"acceptor"`
}
