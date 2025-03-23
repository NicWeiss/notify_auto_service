package acceptor_service

import (
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

func GetAcceptors(user models.User) ([]models.Acceptor, error) {
	var records, err = crud.GetAcceptorsByUserID(user.Id)

	if err != nil {
		return records, err
	}

	return records, err
}
