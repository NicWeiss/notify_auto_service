package session_service

import (
	"crypto/rand"
	"encoding/hex"
	"encoding/json"
	"time"

	"notifier/src/core"
	"notifier/src/integrations/location"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
)

const (
	webSessionLifetime    = 24 * time.Hour
	mobileSessionLifetime = 3650 * 24 * time.Hour
)

func newToken() (string, error) {
	raw := make([]byte, 16)
	if _, err := rand.Read(raw); err != nil {
		return "", err
	}

	return hex.EncodeToString(raw), nil
}

// SetSession creates a session for the user and returns its token.
func SetSession(userId int, userIp string, client *models.ClientInfo) (string, error) {
	lifetime := webSessionLifetime
	if client != nil && client.Mobile != nil && *client.Mobile {
		lifetime = mobileSessionLifetime
	}

	token, err := newToken()
	if err != nil {
		return "", err
	}

	var clientJson json.RawMessage
	if client != nil {
		if clientJson, err = json.Marshal(client); err != nil {
			return "", err
		}
	}

	locationJson := location.Lookup(core.Cfg.IPLocationToken, userIp)

	_, err = crud.CreateSession(core.DB, token, userId, clientJson, locationJson, time.Now().Add(lifetime))
	if err != nil {
		return "", err
	}

	return token, nil
}

// GetUserBySession returns the owner of an alive session.
func GetUserBySession(token string) (models.User, error) {
	return crud.GetUserBySession(core.DB, token, time.Now())
}

func GetSessions(user models.User) ([]models.Session, error) {
	return crud.GetSessionsByUserId(core.DB, user.Id)
}

func DeleteSession(user models.User, sessionId int) error {
	found, err := crud.DeleteSession(core.DB, sessionId, user.Id)
	if err != nil {
		return err
	}
	if !found {
		return apperr.NotFound("Session not found")
	}

	return nil
}

func ClearExpiredSessions() error {
	return crud.ClearExpiredSessions(core.DB, time.Now())
}
