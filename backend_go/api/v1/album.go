package v1

import (
    "net/http"
    "github.com/gin-gonic/gin"
)
import "notifier/services"
import "notifier/api/api_utils"

// CreateTags		godoc
// @Summary			Get albums
// @Description		Get list of albums
// @Produce			application/json
// @Success         200  {array}  services.album
// @Failure         404  {object}  api_utils.ErrorResponse
// @Tags			API
// @Router			/albums [get]
func getAlbums(c *gin.Context) {
    var albums = services.GetAlbums()

    if albums != nil {
        api_utils.SendApiError(
            c,
            http.StatusUnauthorized,
            api_utils.ErrorResponse{
                Error: api_utils.ErrorData{
                    Description: "You are not authorized!",
                    Code: "api_error",
                },
            },
        )
        return
    }

    c.IndentedJSON(http.StatusOK, albums)
}

func DefineAlbum(router *gin.Engine) {
    router.GET("/albums", getAlbums)
}