package endpoints

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"net/http"
	"notifier/src/api/api_utils"
	"notifier/src/api/v1/responses"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/models"
	"notifier/src/services/category_service"
	"notifier/src/utils/decorators"
)

// CreateTags     godoc
// @Summary       Getting notify categories
// @Description   Getting notify categories which user created
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Success       200  {object}   schemas.EmberCategories "List of categories"
// @Failure       400  {object}  api_utils.ErrorResponse
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /categories [get]
func getListOfCategories(c *gin.Context, user models.User) {

	var categories, categoryError = category_service.GetCategories(user)
	if categoryError != nil {
		fmt.Println(categoryError)
		api_utils.SendApiError(c, http.StatusUnauthorized, "category_error", "Can't get category list")
		return
	}
	responses.EmberResponse(c, &schemas.EmberCategories{}, categories)
}

func DefineCategories(router *gin.RouterGroup) {
	router.GET("/categories", decorators.WithUser(getListOfCategories))
}
