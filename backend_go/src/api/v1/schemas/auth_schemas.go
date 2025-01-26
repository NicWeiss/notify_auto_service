package schemas

type LoginParams struct {
	Password string `json:"password"`
	Email    string `json:"email"`
}

type SessionResponse struct {
	Session string `json:"session"`
}
