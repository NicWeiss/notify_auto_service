package endpoints

import (
	"net/http"
	"strconv"
	"strings"
	"time"

	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/notify_service"
	"notifier/src/utils/decorators"
)

var dateLayouts = []string{
	time.RFC3339Nano,
	"2006-01-02T15:04:05",
	"2006-01-02T15:04",
	"2006-01-02 15:04:05",
	"2006-01-02",
}

func parseDate(value *string) (*time.Time, bool) {
	if value == nil || strings.TrimSpace(*value) == "" {
		return nil, true
	}

	for _, layout := range dateLayouts {
		if parsed, err := time.Parse(layout, strings.TrimSpace(*value)); err == nil {
			return &parsed, true
		}
	}

	return nil, false
}

func notifyData(c *gin.Context, requireAll bool) (notify_service.NotifyData, bool) {
	var params schemas.NotifyParams
	if !api_utils.BindJSON(c, &params) {
		return notify_service.NotifyData{}, false
	}

	fields := params.Notify
	if requireAll && (fields.Name == nil || fields.Periodic == nil || fields.Time == nil) {
		api_utils.SendApiError(c, http.StatusUnprocessableEntity, "name, periodic and time are required")
		return notify_service.NotifyData{}, false
	}

	autodisableAt, ok := parseDate(fields.AutodisableAt)
	if !ok {
		api_utils.SendApiError(c, http.StatusUnprocessableEntity, "Wrong autodisable_at")
		return notify_service.NotifyData{}, false
	}

	data := notify_service.NotifyData{
		NotifyWrite: crud.NotifyWrite{
			Name:            fields.Name,
			Text:            fields.Text,
			Periodic:        fields.Periodic,
			DayOfWeek:       fields.DayOfWeek,
			Date:            fields.Date,
			Time:            fields.Time,
			RepeateInterval: fields.RepeateInterval,
			IsDisabled:      fields.IsDisabled,
			CategoryId:      fields.CategoryId,
			IsAutodisable:   fields.IsAutodisable,
			AutodisableAt:   autodisableAt,
		},
	}

	if fields.Acceptors != nil {
		data.AcceptorIds = make([]int, 0, len(*fields.Acceptors))
		for _, acceptor := range *fields.Acceptors {
			data.AcceptorIds = append(data.AcceptorIds, acceptor.Id)
		}
	}

	return data, true
}

func queryInt(c *gin.Context, name string) int {
	value, _ := strconv.Atoi(c.Query(name))
	return value
}

// @Summary       Getting notifies
// @Description   Getting notifies of one category (without category_id - notifies without a category)
// @Produce       application/json
// @Param         Session      header  string  true   "Session token"
// @Param         category_id  query   int     false  "Category id"
// @Param         page         query   int     false  "Page number, from 1"
// @Param         per_page     query   int     false  "Page size"
// @Success       200  {object}   schemas.EmberNotifies "List of notifies"
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Notify
// @Router        /notifies [get]
func getListOfNotifies(c *gin.Context, user models.User) {
	var categoryId *int
	if id := queryInt(c, "category_id"); id > 0 {
		categoryId = &id
	}

	notifies, totalPages, err := notify_service.List(user, categoryId, queryInt(c, "page"), queryInt(c, "per_page"))
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "notify", notifies, totalPages)
}

// @Summary       Getting notify
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         id       path    int     true  "Notify id"
// @Success       200  {object}   schemas.EmberNotify
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          Notify
// @Router        /notifies/{id} [get]
func getNotify(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	notify, err := notify_service.Get(user, id)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "notify", notify, 0)
}

// @Summary       Create notify
// @Accept        application/json
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         payload  body    schemas.NotifyParams  true  "Notify"
// @Success       200  {object}   schemas.EmberNotify
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          Notify
// @Router        /notifies [post]
func createNotify(c *gin.Context, user models.User) {
	data, ok := notifyData(c, true)
	if !ok {
		return
	}

	notify, err := notify_service.Create(user, data)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "notify", notify, 0)
}

// @Summary       Update notify
// @Accept        application/json
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         id       path    int     true  "Notify id"
// @Param         payload  body    schemas.NotifyParams  true  "Notify"
// @Success       200  {object}   schemas.EmberNotify
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          Notify
// @Router        /notifies/{id} [put]
func updateNotify(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	data, ok := notifyData(c, false)
	if !ok {
		return
	}

	notify, err := notify_service.Update(user, id, data)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "notify", notify, 0)
}

// @Summary       Delete notify
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         id       path    int     true  "Notify id"
// @Success       200  {object}   schemas.EmberNotify
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          Notify
// @Router        /notifies/{id} [delete]
func deleteNotify(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	if err := notify_service.Delete(user, id); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "notify", true, 0)
}

// @Summary       Remove notifies from category
// @Description   Notifies of the category stay alive but lose the category
// @Accept        application/json
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         payload  body    schemas.NotifyCategoryParams  true  "Category"
// @Success       200  {object}   api_utils.StatusResponse
// @Tags          Notify
// @Router        /notifies/reset_from_category_id [put]
func resetByCategoryId(c *gin.Context, user models.User) {
	var params schemas.NotifyCategoryParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	if err := notify_service.ResetCategory(user, params.CategoryId); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.SendStatusOk(c)
}

// @Summary       Delete notifies of category
// @Accept        application/json
// @Produce       application/json
// @Param         Session  header  string  true  "Session token"
// @Param         payload  body    schemas.NotifyCategoryParams  true  "Category"
// @Success       200  {object}   api_utils.StatusResponse
// @Tags          Notify
// @Router        /notifies/delete_by_category_id [delete]
func deleteByCategoryId(c *gin.Context, user models.User) {
	var params schemas.NotifyCategoryParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	if err := notify_service.DeleteByCategory(user, params.CategoryId); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.SendStatusOk(c)
}

func DefineNotifies(router *gin.RouterGroup) {
	router.GET("/notifies", decorators.WithUser(getListOfNotifies))
	router.GET("/notifies/:id", decorators.WithUser(getNotify))
	router.POST("/notifies", decorators.WithUser(createNotify))
	router.PUT("/notifies/reset_from_category_id", decorators.WithUser(resetByCategoryId))
	router.DELETE("/notifies/delete_by_category_id", decorators.WithUser(deleteByCategoryId))
	router.PUT("/notifies/:id", decorators.WithUser(updateNotify))
	router.DELETE("/notifies/:id", decorators.WithUser(deleteNotify))
}
