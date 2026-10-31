package api_utils

import (
	"log"
	"net/http"

	"github.com/gin-gonic/gin"

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
		SendApiError(c, http.StatusUnprocessableEntity, "Bad request params")
		return false
	}

	return true
}
