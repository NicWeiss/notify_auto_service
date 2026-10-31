// Package apperr contains errors which are safe to show to the api client.
// Any other error is an internal one and ends up as a 500 response.
package apperr

import (
	"database/sql"
	"errors"
)

type Kind int

const (
	KindInvalid Kind = iota + 1
	KindNotFound
)

type Error struct {
	Kind    Kind
	Message string
}

func (e *Error) Error() string {
	return e.Message
}

// Invalid is a wrong request: bad data, wrong password, busy email...
func Invalid(message string) error {
	return &Error{Kind: KindInvalid, Message: message}
}

func NotFound(message string) error {
	return &Error{Kind: KindNotFound, Message: message}
}

func As(err error) (*Error, bool) {
	var appError *Error
	if errors.As(err, &appError) {
		return appError, true
	}

	return nil, false
}

// NotFoundIfNoRows converts sql.ErrNoRows into a NotFound error with message.
func NotFoundIfNoRows(err error, message string) error {
	if errors.Is(err, sql.ErrNoRows) {
		return NotFound(message)
	}

	return err
}

// InvalidIfNoRows converts sql.ErrNoRows into an Invalid error with message.
func InvalidIfNoRows(err error, message string) error {
	if errors.Is(err, sql.ErrNoRows) {
		return Invalid(message)
	}

	return err
}

// Is reports whether err is a "no records" error.
func IsNoRows(err error) bool {
	return errors.Is(err, sql.ErrNoRows)
}
