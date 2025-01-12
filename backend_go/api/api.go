package api

import (
	"github.com/gin-gonic/gin"
	swaggerFiles "github.com/swaggo/files"
	ginSwagger "github.com/swaggo/gin-swagger"

	v1 "notifier/api/v1/endpoints"
	"notifier/docs"
)

func InitApi(router *gin.Engine) {
	DefineApi(router)
	DefineSwagger(router)
}

func DefineApi(router *gin.Engine) {
	v1.DefineAuth(router)
}

func DefineSwagger(router *gin.Engine) {
	docs.SwaggerInfo.BasePath = "/"
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
