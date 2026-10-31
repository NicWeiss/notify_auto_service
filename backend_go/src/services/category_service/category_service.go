package category_service

import (
	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
)

func GetCategories(user models.User) ([]models.Category, error) {
	return crud.GetCategoriesByUserId(core.DB, user.Id)
}

func Create(user models.User, name string, isHidden bool) (models.Category, error) {
	return crud.CreateCategory(core.DB, user.Id, name, isHidden)
}

func Update(user models.User, categoryId int, name string, isHidden bool) (models.Category, error) {
	category, err := crud.UpdateCategory(core.DB, categoryId, user.Id, name, isHidden)
	return category, apperr.NotFoundIfNoRows(err, "Category not found")
}

func Delete(user models.User, categoryId int) error {
	if _, err := crud.GetCategory(core.DB, categoryId, user.Id); err != nil {
		return apperr.NotFoundIfNoRows(err, "Category not found")
	}

	return crud.DeleteCategory(core.DB, categoryId, user.Id)
}
