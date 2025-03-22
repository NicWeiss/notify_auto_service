package utils

import (
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"reflect"
	"strings"
)

func MapRowToModel(rows *sql.Rows, model interface{}) error {
	cols, _ := rows.Columns()
	columns := make([]interface{}, len(cols))
	columnPointers := make([]interface{}, len(cols))

	for i := range columns {
		columnPointers[i] = &columns[i]
	}

	if err := rows.Scan(columnPointers...); err != nil {
		return err
	}

	var v reflect.Value

	if reflect.ValueOf(model).Kind() == reflect.Ptr {
		v = reflect.ValueOf(model)
		if v.Kind() != reflect.Ptr {
			return errors.New("there is need to pass a pointer, not a value, to StructScan destination")
		}
		v = reflect.Indirect(v)
	} else {
		v = reflect.ValueOf(model)
	}

	t := v.Type()

	var m = make(map[string]interface{})
	for i, colName := range cols {
		val := columnPointers[i].(*interface{})
		m[colName] = *val
	}

	for i := 0; i < v.NumField(); i++ {
		field := strings.Split(t.Field(i).Tag.Get("json"), ",")[0]

		if item, ok := m[field]; ok {
			if v.Field(i).CanSet() {
				if item != nil {
					switch v.Field(i).Kind() {
					case reflect.String:
						v.Field(i).SetString(item.(string))
					case reflect.Int:
						v.Field(i).SetInt(item.(int64))
					case reflect.Float32, reflect.Float64:
						v.Field(i).SetFloat(item.(float64))
					case reflect.Ptr:
						if reflect.ValueOf(item).Kind() == reflect.Bool {
							itemBool := item.(bool)
							v.Field(i).Set(reflect.ValueOf(&itemBool))
						}
					case reflect.Struct:
						v.Field(i).Set(reflect.ValueOf(item))
					case reflect.Bool:
						v.Field(i).Set(reflect.ValueOf(item))
					case reflect.Interface:
						var dat map[string]interface{}
						var json_binary = item.([]uint8)

						if len(json_binary) > 0 {
							if err := json.Unmarshal(json_binary, &dat); err != nil {
								fmt.Println("ERROR! Can't decode json field")
								fmt.Println(err)
								dat = nil
							}
						}
						v.Field(i).Set(reflect.ValueOf(dat))
					default:
						fmt.Println("WARNING! Can't map field to model! You need to add case in mapper")
						var model_type = v.Field(i).Kind()
						var db_type = reflect.ValueOf(item).Kind()
						fmt.Println(t.Field(i).Name, ": Model -", model_type, " | DB - ", db_type) // @todo remove after test out the Get methods
					}
				}
			}
		}
	}

	return nil
}
