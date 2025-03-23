package endpoints

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"net/http"
	"notifier/src/api/api_utils"
	"notifier/src/api/v1/responses"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/models"
	"notifier/src/services/acceptor_service"
	"notifier/src/utils/decorators"
)

// CreateTags     godoc
// @Summary       Getting acceptors
// @Description   Getting acceptors which user created
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Success       200  {object}   schemas.EmberAcceptors "List of acceptors"
// @Failure       400  {object}  api_utils.ErrorResponse
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /acceptors [get]
func getListOfAcceptors(c *gin.Context, user models.User) {
	var acceptors, serviceError = acceptor_service.GetAcceptors(user)
	if serviceError != nil {
		fmt.Println(serviceError)
		api_utils.SendApiError(c, http.StatusUnauthorized, "acceptors_error", "Can't get acceptor list")
		return
	}
	responses.EmberResponse(c, &schemas.EmberAcceptors{}, acceptors)
}

func DefineAcceptors(router *gin.RouterGroup) {
	router.GET("/acceptors", decorators.WithUser(getListOfAcceptors))
}
