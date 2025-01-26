package notify_service

import (
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

func GetNotifies(user models.User, categoryId int) ([]models.Notify, error) {
	var err error
	var notifies []models.Notify

	if categoryId >= 0 {
		notifies, err = crud.GetNotifiesByUserIdAndCategoryId(user.Id, categoryId)
	} else {
		notifies, err = crud.GetNotifiesByUserId(user.Id)
	}

	if err != nil {
		return notifies, err
	}

	return notifies, err
}
