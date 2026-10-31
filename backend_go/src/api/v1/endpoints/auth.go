package endpoints

import (
	"net/http"
	"net/url"
	"strings"

	"github.com/gin-gonic/gin"

	"notifier/src/api/api_utils"
	"notifier/src/api/v1/schemas"
	"notifier/src/core"
	"notifier/src/services/auth_service"
	"notifier/src/services/session_service"
	"notifier/src/services/user_service"
)

func clientIp(c *gin.Context) string {
	if ip := c.GetHeader("X-Real-IP"); ip != "" {
		return ip
	}

	return c.ClientIP()
}

// publicAddress returns protocol and domain for links in letters.
func publicAddress(c *gin.Context) (string, string) {
	if core.Cfg.PublicURL != "" {
		if parsed, err := url.Parse(core.Cfg.PublicURL); err == nil && parsed.Host != "" {
			return parsed.Scheme, parsed.Host
		}
	}

	protocol := "http"
	if c.Request.TLS != nil {
		protocol = "https"
	}
	if forwarded := c.GetHeader("X-Forwarded-Proto"); forwarded != "" {
		protocol = strings.TrimSpace(strings.Split(forwarded, ",")[0])
	}

	domain := c.Request.Host
	if forwarded := c.GetHeader("X-Forwarded-Host"); forwarded != "" {
		domain = strings.TrimSpace(strings.Split(forwarded, ",")[0])
	}

	return protocol, domain
}

// @Summary       Login in system
// @Description   Login, create new session and return its token
// @Accept        application/json
// @Produce       application/json
// @Param         payload   body    schemas.LoginParams  true  "Login credentials"
// @Success       200  {object}   schemas.SessionResponse  "Session token"
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          Auth
// @Router        /auth/login [post]
func login(c *gin.Context) {
	var params schemas.LoginParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	user, err := user_service.GetByPasswordAndEmail(params.Password, params.Email)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	session, err := session_service.SetSession(user.Id, clientIp(c), params.Client)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	c.JSON(http.StatusOK, schemas.SessionResponse{Session: session})
}

// @Summary       Sign up
// @Description   Register a new user by the code from the letter and create a session
// @Accept        application/json
// @Produce       application/json
// @Param         payload   body    schemas.SignUpParams  true  "Registration data"
// @Success       200  {object}   schemas.SessionResponse  "Session token"
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          Auth
// @Router        /auth/sign_up [post]
func signUp(c *gin.Context) {
	var params schemas.SignUpParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	if err := auth_service.VerifyCodeAndEmail(params.Code, params.Email); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	user, err := user_service.Register(params.Name, params.Email, params.Password)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	session, err := session_service.SetSession(user.Id, clientIp(c), params.Client)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	if err = auth_service.ClearRegCode(params.Code, params.Email); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	c.JSON(http.StatusOK, schemas.SessionResponse{Session: session})
}

// @Summary       Get registration code
// @Description   Send a code for the registration to the email
// @Accept        application/json
// @Produce       application/json
// @Param         payload   body    schemas.CodeRequestParams  true  "Email"
// @Success       200  {object}   api_utils.StatusResponse
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          Auth
// @Router        /auth/get_code [post]
func getCode(c *gin.Context) {
	var params schemas.CodeRequestParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	if err := auth_service.SendVerifyCode(params.Email); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	api_utils.SendStatusOk(c)
}

// @Summary       Restore password
// @Description   Send a link for the password restoring to the email
// @Accept        application/json
// @Produce       application/json
// @Param         payload   body    schemas.RestoreParams  true  "Email"
// @Success       200  {object}   schemas.DataResponse
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          Auth
// @Router        /auth/restore [post]
func restore(c *gin.Context) {
	var params schemas.RestoreParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	protocol, domain := publicAddress(c)
	if err := auth_service.SendRestoreCode(protocol, domain, params.Email); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": true})
}

// @Summary       Verify restore code
// @Description   Check that the code from the restore link is alive
// @Accept        application/json
// @Produce       application/json
// @Param         payload   body    schemas.RestoreVerifyCodeParams  true  "Code"
// @Success       200  {object}   schemas.DataResponse
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          Auth
// @Router        /auth/restore/verify-restore-code [post]
func verifyRestoreCode(c *gin.Context) {
	var params schemas.RestoreVerifyCodeParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	if _, err := auth_service.VerifyRestoreCode(params.Code); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": gin.H{}})
}

// @Summary       Set new password
// @Description   Change the password by the code from the restore link
// @Accept        application/json
// @Produce       application/json
// @Param         payload   body    schemas.RestorePasswordParams  true  "Code and the new password"
// @Success       200  {object}   schemas.DataResponse
// @Failure       422  {object}  api_utils.ErrorResponse
// @Tags          Auth
// @Router        /auth/restore/restore-password [post]
func restorePassword(c *gin.Context) {
	var params schemas.RestorePasswordParams
	if !api_utils.BindJSON(c, &params) {
		return
	}

	regCode, err := auth_service.VerifyRestoreCode(params.Code)
	if err != nil {
		api_utils.HandleError(c, err)
		return
	}

	if err = user_service.ChangePasswordByEmail(regCode.Email, params.Password); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	if err = auth_service.ClearRegCode(params.Code, regCode.Email); err != nil {
		api_utils.HandleError(c, err)
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": gin.H{}})
}

func DefineAuth(router *gin.RouterGroup) {
	router.POST("/auth/login", login)
	router.POST("/auth/sign_up", signUp)
	router.POST("/auth/get_code", getCode)
	router.POST("/auth/restore", restore)
	router.POST("/auth/restore/verify-restore-code", verifyRestoreCode)
	router.POST("/auth/restore/restore-password", restorePassword)
}
