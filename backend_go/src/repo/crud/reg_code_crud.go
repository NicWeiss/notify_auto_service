package crud

import (
	"time"

	"notifier/src/core"
	"notifier/src/repo/models"
)

const regCodeColumns = `id, coalesce(code, ''), coalesce(email, ''), expire_at`

func scanRegCode(row scanner) (models.RegCode, error) {
	var r models.RegCode
	err := row.Scan(&r.Id, &r.Code, &r.Email, &r.ExpireAt)
	return r, err
}

func CreateRegCode(q core.Querier, code string, email string, expireAt time.Time) error {
	_, err := q.Exec(
		`insert into reg_code (code, email, expire_at, is_deleted) values ($1, $2, $3, false)`,
		code, email, expireAt.UTC(),
	)
	return err
}

func GetActualRegCodeByEmail(q core.Querier, email string, now time.Time) (models.RegCode, error) {
	return scanRegCode(q.QueryRow(
		`select `+regCodeColumns+` from reg_code where email=$1 and expire_at > $2 limit 1`,
		email, now.UTC(),
	))
}

func GetActualRegCodeByCodeAndEmail(q core.Querier, code string, email string, now time.Time) (models.RegCode, error) {
	return scanRegCode(q.QueryRow(
		`select `+regCodeColumns+` from reg_code where email=$1 and code=$2 and expire_at > $3 limit 1`,
		email, code, now.UTC(),
	))
}

func GetActualRegCodeByCode(q core.Querier, code string, now time.Time) (models.RegCode, error) {
	return scanRegCode(q.QueryRow(
		`select `+regCodeColumns+` from reg_code where code=$1 and expire_at > $2 and is_deleted is not true limit 1`,
		code, now.UTC(),
	))
}

func RemoveRegCode(q core.Querier, code string, email string) error {
	_, err := q.Exec(`delete from reg_code where code=$1 and email=$2`, code, email)
	return err
}

func ClearExpiredRegCodes(q core.Querier, now time.Time) error {
	_, err := q.Exec(`delete from reg_code where expire_at < $1`, now.UTC())
	return err
}
