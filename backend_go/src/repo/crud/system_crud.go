package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
)

const systemColumns = `id, coalesce(is_deleted, false), coalesce(is_disabled, false), coalesce(is_system, false), coalesce(name, ''), coalesce(help, ''), coalesce(type, '')`

func scanSystem(row scanner) (models.System, error) {
	var s models.System
	err := row.Scan(&s.Id, &s.IsDeleted, &s.IsDisabled, &s.IsSystem, &s.Name, &s.Help, &s.Type)
	return s, err
}

func GetSystems(q core.Querier) ([]models.System, error) {
	rows, err := q.Query(
		`select ` + systemColumns + ` from system where is_disabled is not true and is_deleted is not true order by id`,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	systems := make([]models.System, 0)
	for rows.Next() {
		s, err := scanSystem(rows)
		if err != nil {
			return nil, err
		}
		systems = append(systems, s)
	}

	return systems, rows.Err()
}

func GetSystemById(q core.Querier, id int) (models.System, error) {
	return scanSystem(q.QueryRow(`select `+systemColumns+` from system where id=$1 and is_deleted is not true`, id))
}

func GetSystemByType(q core.Querier, systemType string) (models.System, error) {
	return scanSystem(q.QueryRow(
		`select `+systemColumns+` from system where type=$1 and is_deleted is not true limit 1`,
		systemType,
	))
}
