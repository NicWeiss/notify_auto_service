package endpoints

import (
	"net/http"

	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/models"
	"notifier/src/services/user_service"
	"notifier/src/utils/decorators"
)

// @Summary       Getting user
// @Description   Getting data about current authorized user
// @Produce       application/json
// @Param         Session      header  string  true   "Session token"
// @Success       200  {object}   schemas.EmberUser "Current user"
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          User
// @Router        /user [get]
func getCurrentUser(c *gin.Context, user models.User) {
	api_utils.Ember(c, "user", user, 0)
}

// @Summary       Update user
// @Accept        application/json
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         id       path    int     true  "User id (only the current user can be changed)"
// @Param         payload  body    schemas.UserUpdateParams  true  "Name and email"
// @Success       200  {object}   schemas.EmberUser
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          User
// @Router        /users/{id} [put]
func updateUser(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}
	if id != user.Id {
		api_utils.SendApiError(c, http.StatusNotFound, "User not found")
		return
	}

	var params schemas.UserUpdateParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	updated, err := user_service.UpdateProfile(user, params.User.Name, params.User.Email)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "user", updated, 0)
}

// @Summary       Change password
// @Accept        application/json
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         payload  body    schemas.ChangePasswordParams  true  "Passwords"
// @Success       200  {object}   schemas.DataResponse
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          User
// @Router        /user/change-password [post]
func changePassword(c *gin.Context, user models.User) {
	var params schemas.ChangePasswordParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	if err := user_service.ChangePassword(user, params.CurrentPassword, params.NewPassword); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": gin.H{}})
}

func DefineUsers(router *gin.RouterGroup) {
	router.GET("/user", decorators.WithUser(getCurrentUser))
	router.PUT("/users/:id", decorators.WithUser(updateUser))
	router.POST("/user/change-password", decorators.WithUser(changePassword))
}
