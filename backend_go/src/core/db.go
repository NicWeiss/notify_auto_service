package core

import (
	"database/sql"
	"errors"
	"fmt"
	"log"
	"net/url"
	"strings"
	"time"

	_ "github.com/lib/pq"
)

// Querier is implemented by both *sql.DB and *sql.Tx, so the crud layer can
// run inside or outside of a transaction.
type Querier interface {
	Exec(query string, args ...any) (sql.Result, error)
	Query(query string, args ...any) (*sql.Rows, error)
	QueryRow(query string, args ...any) *sql.Row
}

var DB *sql.DB

func InitDBConnection() error {
	db, err := sql.Open("postgres", Cfg.DatabaseURL)
	if err != nil {
		return redact(err)
	}

	db.SetMaxOpenConns(50)
	db.SetMaxIdleConns(10)
	db.SetConnMaxLifetime(30 * time.Minute)

	// the database container may still be starting
	for attempt := 1; attempt <= 30; attempt++ {
		if err = db.Ping(); err == nil {
			break
		}

		log.Printf("waiting for database (%d/30): %v", attempt, redact(err))
		time.Sleep(time.Second)
	}

	if err != nil {
		return fmt.Errorf("can't connect to database: %w", redact(err))
	}

	DB = db
	log.Println("Successfully connected to database")

	return nil
}

// InTx runs fn in a transaction, rolling back on error or panic.
func InTx(fn func(tx *sql.Tx) error) (err error) {
	tx, err := DB.Begin()
	if err != nil {
		return err
	}

	defer func() {
		if p := recover(); p != nil {
			tx.Rollback()
			panic(p)
		}
	}()

	if err = fn(tx); err != nil {
		tx.Rollback()
		return err
	}

	return tx.Commit()
}

// redact removes the database password from an error text: drivers like to
// quote the connection string.
func redact(err error) error {
	if err == nil {
		return nil
	}

	message := err.Error()
	if parsed, parseErr := url.Parse(Cfg.DatabaseURL); parseErr == nil && parsed.User != nil {
		if password, ok := parsed.User.Password(); ok && password != "" {
			message = strings.ReplaceAll(message, password, "***")
			message = strings.ReplaceAll(message, url.QueryEscape(password), "***")
		}
	}

	return errors.New(message)
}
