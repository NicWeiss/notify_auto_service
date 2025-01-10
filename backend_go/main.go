package main

import (
    "github.com/gin-gonic/gin"
     swaggerFiles "github.com/swaggo/files"
     ginSwagger "github.com/swaggo/gin-swagger"
)

import "notifier/api"
import "notifier/docs"


func main() {
    router := gin.Default()
    api.DefineApi(router)

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

    router.Run("localhost:8080")
}