package session_service

import (
	"time"

	"github.com/google/uuid"

	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/repo/schemas"
)

func SetSession(user models.User) (models.Session, error) {
	var now = time.Now()
	var expire_date = now.AddDate(0, 0, 1)

	var session_data = schemas.SessionCreate{
		Session:  uuid.New().String(),
		ExpireAt: expire_date,
		UserId:   user.Id,
	}

	var session_model, err = crud.CreateSession(session_data)

	if err != nil {
		return models.Session{}, err
	}

	return session_model, nil
}
