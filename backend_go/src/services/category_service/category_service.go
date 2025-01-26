package category_service

import (
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

func GetCategories(user models.User) ([]models.Category, error) {
	var categories, err = crud.GetCategoriesByUserID(user.Id)

	if err != nil {
		return categories, err
	}

	return categories, err
}
