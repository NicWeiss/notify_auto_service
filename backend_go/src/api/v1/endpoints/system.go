package endpoints

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"net/http"
	"notifier/src/api/api_utils"
	"notifier/src/api/v1/responses"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/models"
	"notifier/src/services/system_service"
	"notifier/src/utils/decorators"
)

// CreateTags     godoc
// @Summary       Getting systems
// @Description   Getting systems available for sending
// @Produce       application/json
// @Success       200  {object}   schemas.EmberSystems "List of systems"
// @Failure       400  {object}  api_utils.ErrorResponse
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /systems [get]
func getListOfSystems(c *gin.Context, user models.User) {
	var systems, getErr = system_service.GetSystems()
	if getErr != nil {
		fmt.Println(getErr)
		api_utils.SendApiError(c, http.StatusUnauthorized, "notify_error", "Can't get system list")
		return
	}
	responses.EmberResponse(c, &schemas.EmberSystems{}, systems)
}

func DefineSystems(router *gin.RouterGroup) {
	router.GET("/systems", decorators.WithUser(getListOfSystems))
}
