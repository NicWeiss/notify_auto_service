package api

import (
	"github.com/gin-gonic/gin"
	swaggerFiles "github.com/swaggo/files"
	ginSwagger "github.com/swaggo/gin-swagger"

	v1 "notifier/src/api/v1/endpoints"
	"notifier/src/docs"
)

var Prefix = "/api/v3"

// LegacyPrefix is the path of the python backend, old clients (the mobile
// application) still use it.
var LegacyPrefix = "/api/v2"

func InitApi(router *gin.Engine) {
	DefineApi(router)
	DefineSwagger(router)
}

func DefineApi(router *gin.Engine) {
	for _, prefix := range []string{Prefix, LegacyPrefix} {
		apiRouter := router.Group(prefix)
		{
			v1.DefineAuth(apiRouter)
			v1.DefineCategories(apiRouter)
			v1.DefineNotifies(apiRouter)
			v1.DefineSessions(apiRouter)
			v1.DefineUsers(apiRouter)
			v1.DefineSystems(apiRouter)
			v1.DefineAcceptors(apiRouter)
		}
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
