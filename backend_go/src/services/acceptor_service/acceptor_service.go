package acceptor_service

import (
	"database/sql"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
	"notifier/src/services/fcm_service"
)

func GetAcceptors(user models.User, onlyEnabled bool) ([]models.Acceptor, error) {
	return crud.GetAcceptorsByUserId(core.DB, user.Id, onlyEnabled)
}

func Create(user models.User, data crud.AcceptorCreate) (models.Acceptor, error) {
	if _, err := crud.GetSystemById(core.DB, data.SystemId); err != nil {
		return models.Acceptor{}, apperr.InvalidIfNoRows(err, "System not found")
	}

	data.UserId = user.Id
	return crud.CreateAcceptor(core.DB, data)
}

func Update(user models.User, acceptorId int, data crud.AcceptorUpdate) (models.Acceptor, error) {
	if data.SystemId != nil {
		if _, err := crud.GetSystemById(core.DB, *data.SystemId); err != nil {
			return models.Acceptor{}, apperr.InvalidIfNoRows(err, "System not found")
		}
	}

	if _, err := crud.GetAcceptor(core.DB, acceptorId, user.Id); err != nil {
		return models.Acceptor{}, apperr.NotFoundIfNoRows(err, "Acceptor not found")
	}

	return crud.UpdateAcceptor(core.DB, acceptorId, user.Id, data)
}

func Delete(user models.User, acceptorId int) error {
	if _, err := crud.GetAcceptor(core.DB, acceptorId, user.Id); err != nil {
		return apperr.NotFoundIfNoRows(err, "Acceptor not found")
	}

	return core.InTx(func(tx *sql.Tx) error {
		if err := crud.DeleteNotifyAcceptorsByAcceptorId(tx, acceptorId); err != nil {
			return err
		}

		return crud.DeleteAcceptor(tx, acceptorId, user.Id)
	})
}

func UpdatePushTokens(user models.User, token string) error {
	return fcm_service.AddToken(user.Id, token)
}
