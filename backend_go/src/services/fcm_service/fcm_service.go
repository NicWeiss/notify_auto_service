// Package fcm_service keeps firebase push tokens of a user in the account
// field of its "push" acceptor (tokens are separated by ';').
package fcm_service

import (
	"strings"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
)

const tokenSeparator = ";"

func splitTokens(account string) []string {
	tokens := make([]string, 0)
	for _, token := range strings.Split(account, tokenSeparator) {
		if token != "" {
			tokens = append(tokens, token)
		}
	}

	return tokens
}

func getPushAcceptor(userId int) (models.System, models.Acceptor, bool, error) {
	system, err := crud.GetSystemByType(core.DB, "push")
	if err != nil {
		return system, models.Acceptor{}, false, apperr.NotFoundIfNoRows(err, "Push system not found")
	}

	acceptor, err := crud.GetAcceptorByUserIdAndSystemId(core.DB, userId, system.Id)
	if apperr.IsNoRows(err) {
		return system, acceptor, false, nil
	}

	return system, acceptor, err == nil, err
}

func setTokens(userId int, system models.System, acceptor models.Acceptor, exists bool, tokens []string) error {
	account := strings.Join(tokens, tokenSeparator)

	if exists {
		return crud.SetAcceptorAccount(core.DB, acceptor.Id, account)
	}

	_, err := crud.CreateAcceptor(core.DB, crud.AcceptorCreate{
		UserId:   userId,
		SystemId: system.Id,
		Name:     "Push",
		Account:  account,
		IsSystem: true,
	})

	return err
}

func AddToken(userId int, token string) error {
	system, acceptor, exists, err := getPushAcceptor(userId)
	if err != nil {
		return err
	}

	tokens := splitTokens(acceptor.Account)
	for _, known := range tokens {
		if known == token {
			return nil
		}
	}

	return setTokens(userId, system, acceptor, exists, append(tokens, token))
}

func RemoveToken(userId int, token string) error {
	system, acceptor, exists, err := getPushAcceptor(userId)
	if err != nil || !exists {
		return err
	}

	kept := make([]string, 0)
	for _, known := range splitTokens(acceptor.Account) {
		if known != token {
			kept = append(kept, known)
		}
	}

	return setTokens(userId, system, acceptor, exists, kept)
}
