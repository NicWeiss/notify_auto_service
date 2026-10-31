package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
)

const acceptorColumns = `a.id, coalesce(a.is_deleted, false), coalesce(a.is_disabled, false), coalesce(a.is_system, false),
	coalesce(a.name, ''), coalesce(a.user_id, 0), coalesce(a.system_id, 0), coalesce(a.account, ''), coalesce(s.type, '')`

const acceptorFrom = ` from acceptor a left join system s on s.id = a.system_id `

func scanAcceptor(row scanner) (models.Acceptor, error) {
	var a models.Acceptor
	err := row.Scan(&a.Id, &a.IsDeleted, &a.IsDisabled, &a.IsSystem, &a.Name, &a.UserId, &a.SystemId, &a.Account, &a.SystemType)
	return a, err
}

func GetAcceptorsByUserId(q core.Querier, userId int, onlyEnabled bool) ([]models.Acceptor, error) {
	sql := `select ` + acceptorColumns + acceptorFrom + `where a.user_id=$1 and a.is_deleted is not true`
	if onlyEnabled {
		sql += ` and a.is_disabled is not true`
	}

	rows, err := q.Query(sql+` order by a.id`, userId)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	acceptors := make([]models.Acceptor, 0)
	for rows.Next() {
		a, err := scanAcceptor(rows)
		if err != nil {
			return nil, err
		}
		acceptors = append(acceptors, a)
	}

	return acceptors, rows.Err()
}

func GetAcceptor(q core.Querier, id int, userId int) (models.Acceptor, error) {
	return scanAcceptor(q.QueryRow(
		`select `+acceptorColumns+acceptorFrom+`where a.id=$1 and a.user_id=$2 and a.is_deleted is not true`,
		id, userId,
	))
}

func GetAcceptorByUserIdAndSystemId(q core.Querier, userId int, systemId int) (models.Acceptor, error) {
	return scanAcceptor(q.QueryRow(
		`select `+acceptorColumns+acceptorFrom+`where a.user_id=$1 and a.system_id=$2 and a.is_deleted is not true order by a.id limit 1`,
		userId, systemId,
	))
}

type AcceptorCreate struct {
	UserId     int
	SystemId   int
	Name       string
	Account    string
	IsDisabled bool
	IsSystem   bool
}

func CreateAcceptor(q core.Querier, data AcceptorCreate) (models.Acceptor, error) {
	var id int
	err := q.QueryRow(
		`insert into acceptor (user_id, system_id, name, account, is_disabled, is_system, is_deleted)
		 values ($1, $2, $3, $4, $5, $6, false) returning id`,
		data.UserId, data.SystemId, data.Name, data.Account, data.IsDisabled, data.IsSystem,
	).Scan(&id)
	if err != nil {
		return models.Acceptor{}, err
	}

	return GetAcceptor(q, id, data.UserId)
}

// AcceptorUpdate: nil fields are kept as is.
type AcceptorUpdate struct {
	Name       *string
	Account    *string
	IsDisabled *bool
	SystemId   *int
}

func UpdateAcceptor(q core.Querier, id int, userId int, data AcceptorUpdate) (models.Acceptor, error) {
	_, err := q.Exec(
		`update acceptor set name=coalesce($3, name), account=coalesce($4, account),
		 is_disabled=coalesce($5, is_disabled), system_id=coalesce($6, system_id)
		 where id=$1 and user_id=$2`,
		id, userId, data.Name, data.Account, data.IsDisabled, data.SystemId,
	)
	if err != nil {
		return models.Acceptor{}, err
	}

	return GetAcceptor(q, id, userId)
}

func SetAcceptorAccount(q core.Querier, id int, account string) error {
	_, err := q.Exec(`update acceptor set account=$2 where id=$1`, id, account)
	return err
}

func DeleteAcceptor(q core.Querier, id int, userId int) error {
	_, err := q.Exec(`update acceptor set is_deleted=true where id=$1 and user_id=$2`, id, userId)
	return err
}
