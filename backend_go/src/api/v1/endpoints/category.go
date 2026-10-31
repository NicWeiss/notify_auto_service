package endpoints

import (
	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/models"
	"notifier/src/services/category_service"
	"notifier/src/utils/decorators"
)

// @Summary       Getting notify categories
// @Description   Getting notify categories which user created
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Success       200  {object}   schemas.EmberCategories "List of categories"
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /categories [get]
func getListOfCategories(c *gin.Context, user models.User) {
	categories, err := category_service.GetCategories(user)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "category", categories, 0)
}

// @Summary       Create category
// @Accept        application/json
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Param         payload body    schemas.CategoryParams  true  "Category"
// @Success       200  {object}   schemas.EmberCategory
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /categories [post]
func createCategory(c *gin.Context, user models.User) {
	var params schemas.CategoryParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	category, err := category_service.Create(user, params.Category.Name, params.Category.IsHidden)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "category", category, 0)
}

// @Summary       Update category
// @Accept        application/json
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Param         id      path    int     true  "Category id"
// @Param         payload body    schemas.CategoryParams  true  "Category"
// @Success       200  {object}   schemas.EmberCategory
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /categories/{id} [put]
func updateCategory(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	var params schemas.CategoryParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	category, err := category_service.Update(user, id, params.Category.Name, params.Category.IsHidden)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "category", category, 0)
}

// @Summary       Delete category
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Param         id      path    int     true  "Category id"
// @Success       200  {object}   schemas.EmberCategory
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          Category
// @Router        /categories/{id} [delete]
func deleteCategory(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	if err := category_service.Delete(user, id); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "category", true, 0)
}

func DefineCategories(router *gin.RouterGroup) {
	router.GET("/categories", decorators.WithUser(getListOfCategories))
	router.POST("/categories", decorators.WithUser(createCategory))
	router.PUT("/categories/:id", decorators.WithUser(updateCategory))
	router.DELETE("/categories/:id", decorators.WithUser(deleteCategory))
}
