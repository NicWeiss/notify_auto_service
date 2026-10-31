package notify_service

import (
	"database/sql"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
)

// NotifyData is the content of a create or update request. AcceptorIds nil
// means "do not touch the links", an empty list removes all of them.
type NotifyData struct {
	crud.NotifyWrite
	AcceptorIds []int
}

func List(user models.User, categoryId *int, page int, perPage int) ([]models.Notify, int, error) {
	notifies, err := crud.ListNotifies(core.DB, user.Id, categoryId, page, perPage)
	if err != nil {
		return nil, 0, err
	}

	count, err := crud.CountNotifies(core.DB, user.Id, categoryId)
	if err != nil {
		return nil, 0, err
	}

	totalPages := 1
	if perPage > 0 && count > 0 {
		totalPages = (count + perPage - 1) / perPage
	}

	return notifies, totalPages, nil
}

func Get(user models.User, notifyId int) (models.Notify, error) {
	notify, err := crud.GetNotify(core.DB, notifyId, user.Id)
	return notify, apperr.NotFoundIfNoRows(err, "Notify not found")
}

// normalize checks that the category and the acceptors belong to the user.
func normalize(user models.User, data *NotifyData) error {
	if data.CategoryId != nil && *data.CategoryId == 0 {
		data.CategoryId = nil
	}

	if data.CategoryId != nil {
		if _, err := crud.GetCategory(core.DB, *data.CategoryId, user.Id); err != nil {
			return apperr.InvalidIfNoRows(err, "Category not found")
		}
	}

	if len(data.AcceptorIds) > 0 {
		own, err := crud.GetAcceptorsByUserId(core.DB, user.Id, false)
		if err != nil {
			return err
		}

		ownIds := make(map[int]bool, len(own))
		for _, acceptor := range own {
			ownIds[acceptor.Id] = true
		}

		for _, id := range data.AcceptorIds {
			if !ownIds[id] {
				return apperr.Invalid("Acceptor not found")
			}
		}
	}

	return nil
}

func Create(user models.User, data NotifyData) (models.Notify, error) {
	if err := normalize(user, &data); err != nil {
		return models.Notify{}, err
	}

	var notifyId int
	err := core.InTx(func(tx *sql.Tx) error {
		var err error
		if notifyId, err = crud.CreateNotify(tx, user.Id, data.NotifyWrite); err != nil {
			return err
		}

		return crud.ReplaceNotifyAcceptors(tx, notifyId, data.AcceptorIds)
	})
	if err != nil {
		return models.Notify{}, err
	}

	return crud.GetNotify(core.DB, notifyId, user.Id)
}

func Update(user models.User, notifyId int, data NotifyData) (models.Notify, error) {
	if _, err := crud.GetNotify(core.DB, notifyId, user.Id); err != nil {
		return models.Notify{}, apperr.NotFoundIfNoRows(err, "Notify not found")
	}

	if err := normalize(user, &data); err != nil {
		return models.Notify{}, err
	}

	err := core.InTx(func(tx *sql.Tx) error {
		if err := crud.UpdateNotify(tx, notifyId, user.Id, data.NotifyWrite); err != nil {
			return err
		}

		if data.AcceptorIds != nil {
			return crud.ReplaceNotifyAcceptors(tx, notifyId, data.AcceptorIds)
		}

		return nil
	})
	if err != nil {
		return models.Notify{}, err
	}

	return crud.GetNotify(core.DB, notifyId, user.Id)
}

func Delete(user models.User, notifyId int) error {
	if _, err := crud.GetNotify(core.DB, notifyId, user.Id); err != nil {
		return apperr.NotFoundIfNoRows(err, "Notify not found")
	}

	return core.InTx(func(tx *sql.Tx) error {
		if err := crud.DeleteNotifyAcceptorsByNotifyId(tx, notifyId); err != nil {
			return err
		}

		return crud.DeleteNotify(tx, notifyId, user.Id)
	})
}

func ResetCategory(user models.User, categoryId int) error {
	return crud.ResetNotifiesCategory(core.DB, user.Id, categoryId)
}

func DeleteByCategory(user models.User, categoryId int) error {
	return crud.DeleteNotifiesByCategory(core.DB, user.Id, categoryId)
}
