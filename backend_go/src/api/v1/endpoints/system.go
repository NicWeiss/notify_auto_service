package endpoints

import (
	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/repo/models"
	"notifier/src/services/system_service"
	"notifier/src/utils/decorators"
)

// @Summary       Getting systems
// @Description   Getting systems available for sending
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Success       200  {object}   schemas.EmberSystems "List of systems"
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          System
// @Router        /systems [get]
func getListOfSystems(c *gin.Context, user models.User) {
	systems, err := system_service.GetSystems()
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "system", systems, 0)
}

// @Summary       Getting system
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         id       path    int     true  "System id"
// @Success       200  {object}   schemas.EmberSystem
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          System
// @Router        /systems/{id} [get]
func getSystem(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	system, err := system_service.GetSystem(id)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "system", system, 0)
}

func DefineSystems(router *gin.RouterGroup) {
	router.GET("/systems", decorators.WithUser(getListOfSystems))
	router.GET("/systems/:id", decorators.WithUser(getSystem))
}
