package crud

import (
	"notifier/src/core"
	"notifier/src/repo/models"
)

const categoryColumns = `id, coalesce(is_deleted, false), coalesce(name, ''), user_id, coalesce(is_hidden, false)`

func scanCategory(row scanner) (models.Category, error) {
	var c models.Category
	err := row.Scan(&c.Id, &c.IsDeleted, &c.Name, &c.UserId, &c.IsHidden)
	return c, err
}

func GetCategoriesByUserId(q core.Querier, userId int) ([]models.Category, error) {
	rows, err := q.Query(
		`select `+categoryColumns+` from category where user_id=$1 and is_deleted is not true order by id`,
		userId,
	)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	categories := make([]models.Category, 0)
	for rows.Next() {
		c, err := scanCategory(rows)
		if err != nil {
			return nil, err
		}
		categories = append(categories, c)
	}

	return categories, rows.Err()
}

func GetCategory(q core.Querier, id int, userId int) (models.Category, error) {
	return scanCategory(q.QueryRow(
		`select `+categoryColumns+` from category where id=$1 and user_id=$2 and is_deleted is not true`,
		id, userId,
	))
}

func CreateCategory(q core.Querier, userId int, name string, isHidden bool) (models.Category, error) {
	return scanCategory(q.QueryRow(
		`insert into category (user_id, name, is_hidden, is_deleted) values ($1, $2, $3, false) returning `+categoryColumns,
		userId, name, isHidden,
	))
}

func UpdateCategory(q core.Querier, id int, userId int, name string, isHidden bool) (models.Category, error) {
	return scanCategory(q.QueryRow(
		`update category set name=$3, is_hidden=$4 where id=$1 and user_id=$2 and is_deleted is not true returning `+categoryColumns,
		id, userId, name, isHidden,
	))
}

func DeleteCategory(q core.Querier, id int, userId int) error {
	_, err := q.Exec(`update category set is_deleted=true where id=$1 and user_id=$2`, id, userId)
	return err
}
