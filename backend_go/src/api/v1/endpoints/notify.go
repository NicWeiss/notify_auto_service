package endpoints

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/spf13/cast"
	"net/http"
	"notifier/src/api/api_utils"
	"notifier/src/api/v1/responses"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/models"
	"notifier/src/services/notify_service"
	"notifier/src/utils/decorators"
)

// CreateTags     godoc
// @Summary       Getting notifies
// @Description   Getting notify  which user created
// @Produce       application/json
// @Param         category_id  query   int     false  "Id for select only linked notifies"
// @Param         Session      header  string  true   "Session token"
// @Success       200  {object}   schemas.EmberNotifies "List of notifies"
// @Failure       400  {object}  api_utils.ErrorResponse
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /notifies [get]
func getListOfNotifies(c *gin.Context, user models.User) {
	categoryId, isOk := c.GetQuery("category_id")
	if !isOk {
		categoryId = "-1"
	}

	var notifies, getErr = notify_service.GetNotifies(user, cast.ToInt(categoryId))
	if getErr != nil {
		fmt.Println(getErr)
		api_utils.SendApiError(c, http.StatusUnauthorized, "notify_error", "Can't get category list")
		return
	}
	responses.EmberResponse(c, &schemas.EmberNotifies{}, notifies)
}

func DefineNotifies(router *gin.RouterGroup) {
	router.GET("/notifies", decorators.WithUser(getListOfNotifies))
}
