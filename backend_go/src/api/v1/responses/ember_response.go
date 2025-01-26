package responses

import (
	"github.com/fatih/structs"
	"github.com/gin-gonic/gin"
	"net/http"
	"notifier/src/api/v1/schemas"
	"reflect"
)

func setProperty(r interface{}, propName string, propValue any) {
	ref := reflect.ValueOf(r)
	el := ref.Elem()
	field := el.FieldByName(propName)

	field.Set(reflect.ValueOf(propValue))
}

func EmberResponse(c *gin.Context, scheme interface{}, data any) {
	emberMeta := schemas.EmberMeta{TotalPage: 1}
	schemaKeys := structs.Names(scheme)
	var modelName = "UnkownModel"

	for _, element := range schemaKeys {
		if element != "Meta" {
			modelName = element
			break
		}
	}

	setProperty(scheme, modelName, data)
	setProperty(scheme, "Meta", emberMeta)

	c.IndentedJSON(http.StatusOK, scheme)
}
