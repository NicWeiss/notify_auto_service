package core

import (
	"context"
	"embed"
	"fmt"
	"log"
	"sort"
	"strings"
)

//go:embed migrations/*.sql
var migrationFiles embed.FS

const migrationLockId = 4241

// Migrate applies embedded *.sql files in name order. Applied versions are
// stored in schema_migrations. The first migration is idempotent, so databases
// created by the former alembic migrations are adopted without changes.
func Migrate() error {
	conn, err := DB.Conn(context.Background())
	if err != nil {
		return err
	}
	defer conn.Close()

	// only one instance migrates at a time
	if _, err = conn.ExecContext(context.Background(), `select pg_advisory_lock($1)`, migrationLockId); err != nil {
		return err
	}
	defer conn.ExecContext(context.Background(), `select pg_advisory_unlock($1)`, migrationLockId)

	_, err = conn.ExecContext(context.Background(), `create table if not exists schema_migrations (version text primary key, applied_at timestamp default (now() at time zone 'utc'))`)
	if err != nil {
		return err
	}

	entries, err := migrationFiles.ReadDir("migrations")
	if err != nil {
		return err
	}

	names := make([]string, 0, len(entries))
	for _, entry := range entries {
		names = append(names, entry.Name())
	}
	sort.Strings(names)

	for _, name := range names {
		version := strings.TrimSuffix(name, ".sql")

		var applied bool
		err = conn.QueryRowContext(context.Background(), `select exists(select 1 from schema_migrations where version=$1)`, version).Scan(&applied)
		if err != nil {
			return err
		}
		if applied {
			continue
		}

		body, err := migrationFiles.ReadFile("migrations/" + name)
		if err != nil {
			return err
		}

		tx, err := conn.BeginTx(context.Background(), nil)
		if err != nil {
			return err
		}

		if _, err = tx.Exec(string(body)); err == nil {
			_, err = tx.Exec(`insert into schema_migrations (version) values ($1)`, version)
		}
		if err != nil {
			tx.Rollback()
			return fmt.Errorf("migration %s: %w", version, err)
		}
		if err = tx.Commit(); err != nil {
			return err
		}

		log.Printf("migration %s applied", version)
	}

	return nil
}
