package system_service

import (
	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
)

func GetSystems() ([]models.System, error) {
	return crud.GetSystems(core.DB)
}

func GetSystem(id int) (models.System, error) {
	system, err := crud.GetSystemById(core.DB, id)
	return system, apperr.NotFoundIfNoRows(err, "System not found")
}
