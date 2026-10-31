package endpoints

import (
	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/api/v1/schemas"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
	"notifier/src/services/acceptor_service"
	"notifier/src/utils/decorators"
)

// @Summary       Getting acceptors
// @Description   Getting acceptors which user created
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Param         only_enabled  query  bool  false  "Skip disabled acceptors"
// @Success       200  {object}   schemas.EmberAcceptors "List of acceptors"
// @Failure       401  {object}  api_utils.ErrorResponse
// @Tags          Acceptor
// @Router        /acceptors [get]
func getListOfAcceptors(c *gin.Context, user models.User) {
	onlyEnabled := c.Query("only_enabled") == "true" || c.Query("only_enabled") == "1"

	acceptors, err := acceptor_service.GetAcceptors(user, onlyEnabled)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "acceptor", acceptors, 0)
}

// @Summary       Create acceptor
// @Accept        application/json
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Param         payload body    schemas.AcceptorCreateParams  true  "Acceptor"
// @Success       200  {object}   schemas.EmberAcceptor
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          Acceptor
// @Router        /acceptors [post]
func createAcceptor(c *gin.Context, user models.User) {
	var params schemas.AcceptorCreateParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	fields := params.Acceptor
	acceptor, err := acceptor_service.Create(user, crud.AcceptorCreate{
		SystemId:   fields.SystemId,
		Name:       fields.Name,
		Account:    fields.Account,
		IsDisabled: fields.IsDisabled,
		IsSystem:   fields.IsSystem,
	})
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "acceptor", acceptor, 0)
}

// @Summary       Update acceptor
// @Accept        application/json
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Param         id      path    int     true  "Acceptor id"
// @Param         payload body    schemas.AcceptorUpdateParams  true  "Acceptor"
// @Success       200  {object}   schemas.EmberAcceptor
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          Acceptor
// @Router        /acceptors/{id} [put]
func updateAcceptor(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	var params schemas.AcceptorUpdateParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	fields := params.Acceptor
	acceptor, err := acceptor_service.Update(user, id, crud.AcceptorUpdate{
		Name:       fields.Name,
		Account:    fields.Account,
		IsDisabled: fields.IsDisabled,
		SystemId:   fields.SystemId,
	})
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "acceptor", acceptor, 0)
}

// @Summary       Delete acceptor
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Param         id      path    int     true  "Acceptor id"
// @Success       200  {object}   schemas.EmberAcceptor
// @Failure       404  {object}  api_utils.ErrorResponse
// @Tags          Acceptor
// @Router        /acceptors/{id} [delete]
func deleteAcceptor(c *gin.Context, user models.User) {
	id, ok := pathId(c)
	if !ok {
		return
	}

	if err := acceptor_service.Delete(user, id); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "acceptor", true, 0)
}

// @Summary       Add push token
// @Description   Add a firebase token of the device to the push acceptor
// @Accept        application/json
// @Produce       application/json
// @Param         Session header  string  true  "Session token"
// @Param         payload body    schemas.PushTokenParams  true  "Token"
// @Success       200  {object}   schemas.EmberAcceptor
// @Tags          Acceptor
// @Router        /acceptors/update_push_tokens [post]
func updatePushTokens(c *gin.Context, user models.User) {
	var params schemas.PushTokenParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	if err := acceptor_service.UpdatePushTokens(user, params.Token); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.Ember(c, "acceptor", true, 0)
}

func DefineAcceptors(router *gin.RouterGroup) {
	router.GET("/acceptors", decorators.WithUser(getListOfAcceptors))
	router.POST("/acceptors", decorators.WithUser(createAcceptor))
	router.POST("/acceptors/update_push_tokens", decorators.WithUser(updatePushTokens))
	router.PUT("/acceptors/:id", decorators.WithUser(updateAcceptor))
	router.DELETE("/acceptors/:id", decorators.WithUser(deleteAcceptor))
}
