package decorators

import (
	"github.com/gin-gonic/gin"
	"net/http"
	"notifier/src/api/api_utils"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

type methodType func(*gin.Context, models.User)

func WithUser(apiMethod methodType) func(c *gin.Context) {
	return func(c *gin.Context) {
		session, isOk := c.Request.Header["Session"]
		if !isOk {
			api_utils.SendApiError(c, http.StatusUnauthorized, "auth_error", "Not authorized")
			return
		}

		sessionString := session[0]
		user, err := crud.GetUserBySession(sessionString)
		if err != nil {
			api_utils.SendApiError(c, http.StatusUnauthorized, "auth_error", "Not authorized")
			return
		}

		apiMethod(c, user)
	}
}
