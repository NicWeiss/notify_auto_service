package main

import (
	"github.com/gin-gonic/gin"

	"notifier/src/api"
	"notifier/src/core"
)

func main() {
	router := gin.Default()
	core.InitDBConnection()
	api.InitApi(router)

	router.Run("localhost:8080")
}
