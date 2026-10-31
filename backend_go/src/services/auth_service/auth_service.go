package auth_service

import (
	"crypto/rand"
	"encoding/hex"
	"log"
	"math/big"
	"strconv"
	"time"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
	"notifier/src/services/email_service"
	"notifier/src/templates"
)

const codeLifetime = 3 * time.Minute

func ensureNoActualCode(email string) error {
	_, err := crud.GetActualRegCodeByEmail(core.DB, email, time.Now())
	if err == nil {
		return apperr.Invalid("Code already sent")
	}
	if !apperr.IsNoRows(err) {
		return err
	}

	return nil
}

// sendCode stores the code first, so a code from a delivered letter is always
// valid. If the letter is not sent the code is removed to let the user retry.
func sendCode(email string, code string, subject string, html string) error {
	err := crud.CreateRegCode(core.DB, code, email, time.Now().Add(codeLifetime))
	if err != nil {
		return err
	}

	if err = email_service.Send(email, subject, "", html); err != nil {
		log.Printf("SMTP error: %v", err)
		crud.RemoveRegCode(core.DB, code, email)
		return apperr.Invalid("Error while sending code")
	}

	return nil
}

func SendVerifyCode(email string) error {
	_, err := crud.GetUserByEmail(core.DB, email)
	if err == nil {
		return apperr.Invalid("Email is busy")
	}
	if !apperr.IsNoRows(err) {
		return err
	}

	if err = ensureNoActualCode(email); err != nil {
		return err
	}

	number, err := rand.Int(rand.Reader, big.NewInt(9000))
	if err != nil {
		return err
	}
	code := strconv.Itoa(1000 + int(number.Int64()))

	return sendCode(email, code, "Registration code", templates.RegCodeEmail(code))
}

func VerifyCodeAndEmail(code string, email string) error {
	_, err := crud.GetUserByEmail(core.DB, email)
	if err == nil {
		return apperr.Invalid("Email is busy")
	}
	if !apperr.IsNoRows(err) {
		return err
	}

	_, err = crud.GetActualRegCodeByCodeAndEmail(core.DB, code, email, time.Now())
	return apperr.InvalidIfNoRows(err, "Code wrong or expired")
}

func VerifyRestoreCode(code string) (models.RegCode, error) {
	regCode, err := crud.GetActualRegCodeByCode(core.DB, code, time.Now())
	return regCode, apperr.InvalidIfNoRows(err, "Restore code is wrong")
}

func ClearRegCode(code string, email string) error {
	return crud.RemoveRegCode(core.DB, code, email)
}

// SendRestoreCode mails a link for the password restoring. The token in the
// link is random, a short numeric code would be easy to guess.
func SendRestoreCode(protocol string, domain string, email string) error {
	_, err := crud.GetUserByEmail(core.DB, email)
	if err != nil {
		return apperr.InvalidIfNoRows(err, "User with this email not found")
	}

	if err = ensureNoActualCode(email); err != nil {
		return err
	}

	raw := make([]byte, 20)
	if _, err = rand.Read(raw); err != nil {
		return err
	}
	token := hex.EncodeToString(raw)

	return sendCode(email, token, "Restoration code", templates.RestoreLinkEmail(protocol, domain, token))
}

func ClearExpiredCodes() error {
	return crud.ClearExpiredRegCodes(core.DB, time.Now())
}
