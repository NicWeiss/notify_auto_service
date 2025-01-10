package api

import (
     "github.com/gin-gonic/gin"
)

import "notifier/api/v1"


func DefineApi(router *gin.Engine) {
    v1.DefineAlbum(router)
    v1.DefineTracks(router)
}