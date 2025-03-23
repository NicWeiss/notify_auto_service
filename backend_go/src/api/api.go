package api

import (
	"github.com/gin-gonic/gin"
	swaggerFiles "github.com/swaggo/files"
	ginSwagger "github.com/swaggo/gin-swagger"

	v1 "notifier/src/api/v1/endpoints"
	"notifier/src/docs"
)

var Prefix = "/api/v3"

func InitApi(router *gin.Engine) {
	DefineApi(router)
	DefineSwagger(router)
}

func DefineApi(router *gin.Engine) {
	api_router := router.Group(Prefix)
	{
		v1.DefineAuth(api_router)
		v1.DefineCategories(api_router)
		v1.DefineNotifies(api_router)
		v1.DefineSessions(api_router)
		v1.DefineUsers(api_router)
		v1.DefineSystems(api_router)
		v1.DefineAcceptors(api_router)
	}
}

func DefineSwagger(router *gin.Engine) {
	docs.SwaggerInfo.BasePath = Prefix
	docs.SwaggerInfo.Title = "Notifier"
	docs.SwaggerInfo.Description = "A little notification service for yourself"

	router.GET("/docs/*any", func(c *gin.Context) {
		if c.Request.RequestURI == "/docs/" {
			c.Redirect(302, "/docs/index.html")
			return
		}
		ginSwagger.WrapHandler(swaggerFiles.Handler)(c)
	})
}
