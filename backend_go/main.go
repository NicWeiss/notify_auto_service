package main

import (
	"github.com/gin-gonic/gin"

	"notifier/api"
	"notifier/core"
)

func main() {
	router := gin.Default()
	core.InitDBConnection()
	api.InitApi(router)

	router.Run("localhost:8080")
}
