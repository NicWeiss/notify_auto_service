package crud

import (
	"time"

	"notifier/src/core"
	"notifier/src/repo/models"
)

const userColumns = `id, coalesce(is_deleted, false), coalesce(name, ''), coalesce(email, ''), coalesce(password, ''), registred_at`

type scanner interface {
	Scan(dest ...any) error
}

func scanUser(row scanner) (models.User, error) {
	var u models.User
	err := row.Scan(&u.Id, &u.IsDeleted, &u.Name, &u.Email, &u.Password, &u.RegistredAt)
	return u, err
}

// All Get* functions return sql.ErrNoRows when nothing is found.

func GetUserById(q core.Querier, id int) (models.User, error) {
	return scanUser(q.QueryRow(`select `+userColumns+` from "user" where id=$1 and is_deleted is not true`, id))
}

func GetUserByEmail(q core.Querier, email string) (models.User, error) {
	return scanUser(q.QueryRow(`select `+userColumns+` from "user" where email=$1 limit 1`, email))
}

func GetUserByPasswordAndEmail(q core.Querier, passwordHash string, email string) (models.User, error) {
	return scanUser(q.QueryRow(
		`select `+userColumns+` from "user" where password=$1 and email=$2 and is_deleted is not true limit 1`,
		passwordHash, email,
	))
}

func CreateUser(q core.Querier, name string, email string, passwordHash string, registredAt time.Time) (models.User, error) {
	return scanUser(q.QueryRow(
		`insert into "user" (name, email, password, registred_at, is_deleted) values ($1, $2, $3, $4, false) returning `+userColumns,
		name, email, passwordHash, registredAt.UTC(),
	))
}

func UpdateUserProfile(q core.Querier, id int, name *string, email *string) (models.User, error) {
	return scanUser(q.QueryRow(
		`update "user" set name=coalesce($2, name), email=coalesce($3, email) where id=$1 returning `+userColumns,
		id, name, email,
	))
}

func UpdateUserPassword(q core.Querier, id int, passwordHash string) error {
	_, err := q.Exec(`update "user" set password=$2 where id=$1`, id, passwordHash)
	return err
}
