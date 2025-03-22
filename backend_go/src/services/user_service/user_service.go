package user_service

import (
	"crypto/sha1"
	"encoding/hex"

	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

func CryptPassword(password string) string {
	h := sha1.New()
	h.Write([]byte(password))
	sha1_hash := hex.EncodeToString(h.Sum(nil))

	return sha1_hash
}

func GetByPasswordAndEmail(password string, email string) (models.User, error) {
	var md5_pass = CryptPassword(password)
	var user, crudError = crud.GetUserByPasswordAndEmail(md5_pass, email)

	return user, crudError
}
