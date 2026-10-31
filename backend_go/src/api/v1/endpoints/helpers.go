package endpoints

import (
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
)

// pathId reads the :id path parameter.
func pathId(c *gin.Context) (int, bool) {
	id, err := strconv.Atoi(c.Param("id"))
	if err != nil {
		api_utils.SendApiError(c, http.StatusNotFound, "Not found")
		return 0, false
	}

	return id, true
}
