package system_service

import (
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

func GetSystems() ([]models.System, error) {
	var err error
	var systems []models.System

	systems, err = crud.GetSystems()

	return systems, err
}
