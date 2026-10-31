package decorators

import (
	"net/http"

	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/repo/models"
	"notifier/src/services/apperr"
	"notifier/src/services/session_service"
)

type methodType func(*gin.Context, models.User)

// WithUser passes the owner of the Session header to the handler. Requests
// without an alive session are answered with 401.
func WithUser(apiMethod methodType) gin.HandlerFunc {
	return func(c *gin.Context) {
		session := c.GetHeader("Session")
		if session == "" {
			api_utils.SendApiError(c, http.StatusUnauthorized, "Auth required")
			return
		}

		user, err := session_service.GetUserBySession(session)
		if err != nil {
			if apperr.IsNoRows(err) {
				api_utils.SendApiError(c, http.StatusUnauthorized, "Auth required")
			} else {
				api_utils.HandleError(c, err)
			}
			return
		}

		apiMethod(c, user)
	}
}
