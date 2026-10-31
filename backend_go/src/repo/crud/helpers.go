package crud

import (
	"database/sql"
	"strconv"
)

// ErrNotFound is returned by all single row getters when there is no record.
var ErrNotFound = sql.ErrNoRows

var errNoRows = ErrNotFound

func itoa(value int) string {
	return strconv.Itoa(value)
}
