package api_utils

import (
    "github.com/gin-gonic/gin"
)


type SuccessData struct {
  Id  int `json:"id"`
  Description  string `json:"description"`
}

type ErrorData struct {
  Code  string `json:"code"`
  Description  string `json:"description"`
}

type SuccessResponse struct {
  Status string `json:"status"`
  Data SuccessData `json:"data"`
}

type ErrorResponse struct {
  Status string `json:"status"`
  Error ErrorData `json:"error"`
}

func SendApiSuccess(c *gin.Context, status int, response SuccessResponse) {
    response.Status = "ok"
    c.IndentedJSON(status, response)
}

func SendApiError(c *gin.Context, status int, response ErrorResponse) {
    response.Status = "error"
    c.IndentedJSON(status, response)
}