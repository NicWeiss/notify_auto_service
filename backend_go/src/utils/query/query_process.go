package query

import (
	"database/sql"
	//"fmt"

	"github.com/jinzhu/copier"
	"notifier/src/repo/models"
	"notifier/src/utils"
)

type Any = interface{}

func QueryProcess(inst models.Model, rows *sql.Rows, queryErr error) ([]Any, error) {
	defer rows.Close()
	var result = make([]Any, 0)
	var emptyResult = make([]Any, 0)

	if queryErr != nil {
		return emptyResult, queryErr
	}

	for rows.Next() {
		var model2, ptr = inst.GetEmptyItem()
		err := utils.MapRowToModel(rows, ptr)

		copier.Copy(&model2, ptr)

		if err != nil {
			return emptyResult, err
		}

		result = append(result, model2)
	}

	if result == nil {
		return emptyResult, nil
	}

	return result, nil
}
