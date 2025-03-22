package endpoints

import (
	"fmt"
	"net/http"

	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/api/v1/schemas"
	"notifier/src/services/session_service"
	"notifier/src/services/user_service"
)

// CreateTags     godoc
// @Summary       Login in system
// @Description   Login, create new session, return auth cookies
// @Produce       application/json
// @Param         payload   body    schemas.LoginParams    false  "Login credentials"
// @Success       200  {object}   schemas.SessionResponse  "Session token"
// @Failure       400  {object}  api_utils.ErrorResponse
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Auth
// @Router        /auth/login [post]
func login(c *gin.Context) {
	var params schemas.LoginParams

	err := c.BindJSON(&params)
	if err != nil {
		api_utils.SendApiError(c, http.StatusBadRequest, "auth_error", "Bad request params")
		return
	}

	var user, serviceError = user_service.GetByPasswordAndEmail(params.Password, params.Email)
	if serviceError != nil {
		api_utils.SendApiError(c, http.StatusUnauthorized, "auth_error", "Wrong email or password")
		return
	}

	var session, session_err = session_service.SetSession(user)
	if session_err != nil {
		fmt.Println(session_err)
		api_utils.SendApiError(c, http.StatusUnauthorized, "session_error", "Can't create session")
		return
	}

	c.IndentedJSON(http.StatusOK, schemas.SessionResponse{Session: session.Session})
}

func DefineAuth(router *gin.RouterGroup) {
	router.POST("/auth/login", login)
}
