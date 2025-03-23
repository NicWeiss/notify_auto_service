package endpoints

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"net/http"
	"notifier/src/api/api_utils"
	"notifier/src/api/v1/responses"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/models"
	"notifier/src/services/session_service"
	"notifier/src/utils/decorators"
)

// CreateTags     godoc
// @Summary       Getting sessions
// @Description   Getting sessions which user created
// @Produce       application/json
// @Param         Session      header  string  true   "Session token"
// @Success       200  {object}   schemas.EmberSessions "List of sessions"
// @Failure       400  {object}  api_utils.ErrorResponse
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /sessions [get]
func getListOfSessions(c *gin.Context, user models.User) {
	var sessions, getErr = session_service.GetSessions(user)
	if getErr != nil {
		fmt.Println(getErr)
		api_utils.SendApiError(c, http.StatusUnauthorized, "session_error", "Can't get session list")
		return
	}
	responses.EmberResponse(c, &schemas.EmberSessions{}, sessions)
}

func DefineSessions(router *gin.RouterGroup) {
	router.GET("/sessions", decorators.WithUser(getListOfSessions))
}
