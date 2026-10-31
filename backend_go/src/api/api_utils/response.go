package api_utils

import (
	"encoding/json"
	"errors"
	"fmt"
	"log"
	"net/http"
	"strings"

	"github.com/gin-gonic/gin"
	"github.com/go-playground/validator/v10"

	"notifier/src/services/apperr"
)

// ErrorResponse has the same shape as the errors of the former python
// backend, the frontend shows `detail` to the user.
type ErrorResponse struct {
	Detail string `json:"detail"`
}

type StatusResponse struct {
	Status string `json:"status"`
}

type EmberMeta struct {
	TotalPages int `json:"total_pages"`
}

func SendApiError(c *gin.Context, status int, detail string) {
	c.AbortWithStatusJSON(status, ErrorResponse{Detail: detail})
}

// HandleError answers with 422/404 for the errors made by the client and with
// 500 for all the others (without details).
func HandleError(c *gin.Context, err error) {
	if appError, ok := apperr.As(err); ok {
		status := http.StatusUnprocessableEntity
		if appError.Kind == apperr.KindNotFound {
			status = http.StatusNotFound
		}

		SendApiError(c, status, appError.Message)
		return
	}

	log.Printf("%s %s: %v", c.Request.Method, c.Request.URL.Path, err)
	SendApiError(c, http.StatusInternalServerError, "Internal server error")
}

// Ember sends {"meta": {...}, "<modelName>": data}, the format of ember data.
func Ember(c *gin.Context, modelName string, data any, totalPages int) {
	c.JSON(http.StatusOK, gin.H{
		"meta":    EmberMeta{TotalPages: totalPages},
		modelName: data,
	})
}

func SendStatusOk(c *gin.Context) {
	c.JSON(http.StatusOK, StatusResponse{Status: "ok"})
}

// BindJSON reads a request body, a bad one is answered with 422.
func BindJSON(c *gin.Context, destination any) bool {
	if err := c.ShouldBindJSON(destination); err != nil {
		SendApiError(c, http.StatusUnprocessableEntity, badParamsMessage(err))
		return false
	}

	return true
}

// badParamsMessage tells which field is wrong (the values are not shown).
func badParamsMessage(err error) string {
	var typeError *json.UnmarshalTypeError
	if errors.As(err, &typeError) && typeError.Field != "" {
		return fmt.Sprintf("Bad request params: %s must be %s", typeError.Field, typeError.Type)
	}

	var validationErrors validator.ValidationErrors
	if errors.As(err, &validationErrors) && len(validationErrors) > 0 {
		return fmt.Sprintf("Bad request params: %s is required", validationErrors[0].Namespace())
	}

	// a value of the wrong kind in a field with a custom decoder (FlexInt)
	if strings.Contains(err.Error(), "is not an integer") {
		return "Bad request params: " + err.Error()
	}

	return "Bad request params"
}
