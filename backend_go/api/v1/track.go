package v1

import (
    "net/http"
    "github.com/gin-gonic/gin"
)


// CreateTags		godoc
// @Summary			Get tracks
// @Description		Get list of tracks
// @Produce			application/json
// @Tags			API
// @Router			/tracks [get]
func getTracks(c *gin.Context) {
    var tracks = []int{}
    c.IndentedJSON(http.StatusOK, tracks)
}

func DefineTracks(router *gin.Engine) {
    router.GET("/tracks", getTracks)
}