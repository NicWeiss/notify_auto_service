package endpoints

import (
	"github.com/gin-gonic/gin"
	"notifier/src/api/v1/responses"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/models"
	"notifier/src/utils/decorators"
)

// CreateTags     godoc
// @Summary       Getting user
// @Description   Getting data obout current authorized user
// @Produce       application/json
// @Param         Session      header  string  true   "Session token"
// @Success       200  {object}   schemas.EmberUser "Current user"
// @Failure       400  {object}  api_utils.ErrorResponse
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /user [get]
func getCurrentUser(c *gin.Context, user models.User) {
	responses.EmberResponse(c, &schemas.EmberUser{}, user)
}

func DefineUsers(router *gin.RouterGroup) {
	router.GET("/user", decorators.WithUser(getCurrentUser))
}
