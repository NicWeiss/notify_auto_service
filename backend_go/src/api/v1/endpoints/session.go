package endpoints

import (
	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/repo/models"
	"notifier/src/services/session_service"
	"notifier/src/utils/decorators"
)

// @Summary       Getting sessions
// @Description   Getting sessions which user created
// @Produce       application/json
// @Param         Session      header  string  true   "Session token"
// @Success       200  {object}   schemas.EmberSessions "List of sessions"
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Session
// @Router        /sessions [get]
func getListOfSessions(c *gin.Context, user models.User) {
	sessions, err := session_service.GetSessions(user)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "session", sessions, 0)
}

// @Summary       Delete session
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         id       path    int     true  "Session id"
// @Success       200  {object}   schemas.EmberSessions
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          Session
// @Router        /sessions/{id} [delete]
func deleteSession(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	if err := session_service.DeleteSession(user, id); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "session", true, 0)
}

func DefineSessions(router *gin.RouterGroup) {
	router.GET("/sessions", decorators.WithUser(getListOfSessions))
	router.DELETE("/sessions/:id", decorators.WithUser(deleteSession))
}
