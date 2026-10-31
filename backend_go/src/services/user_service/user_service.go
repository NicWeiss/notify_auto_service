package user_service

import (
	"crypto/sha1"
	"encoding/hex"
	"time"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
)

// CryptPassword: sha1 is kept for compatibility with the existing users.
func CryptPassword(password string) string {
	hash := sha1.Sum([]byte(password))
	return hex.EncodeToString(hash[:])
}

func GetByPasswordAndEmail(password string, email string) (models.User, error) {
	user, err := crud.GetUserByPasswordAndEmail(core.DB, CryptPassword(password), email)
	return user, apperr.InvalidIfNoRows(err, "User not found")
}

func Register(name string, email string, password string) (models.User, error) {
	_, err := crud.GetUserByEmail(core.DB, email)
	if err == nil {
		return models.User{}, apperr.Invalid("Email is busy")
	}
	if !apperr.IsNoRows(err) {
		return models.User{}, err
	}

	return crud.CreateUser(core.DB, name, email, CryptPassword(password), time.Now())
}

func ChangePassword(user models.User, currentPassword string, newPassword string) error {
	if user.Password != CryptPassword(currentPassword) {
		return apperr.Invalid("Current password is wrong")
	}

	return crud.UpdateUserPassword(core.DB, user.Id, CryptPassword(newPassword))
}

func ChangePasswordByEmail(email string, password string) error {
	user, err := crud.GetUserByEmail(core.DB, email)
	if err != nil {
		return apperr.InvalidIfNoRows(err, "User not found")
	}

	return crud.UpdateUserPassword(core.DB, user.Id, CryptPassword(password))
}

// UpdateProfile changes name and email, nil values stay as they are.
func UpdateProfile(user models.User, name *string, email *string) (models.User, error) {
	if email != nil && *email != user.Email {
		_, err := crud.GetUserByEmail(core.DB, *email)
		if err == nil {
			return models.User{}, apperr.Invalid("Email is busy")
		}
		if !apperr.IsNoRows(err) {
			return models.User{}, err
		}
	}

	return crud.UpdateUserProfile(core.DB, user.Id, name, email)
}
