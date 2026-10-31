package models

// ClientInfo describes the device a session was created from.
type ClientInfo struct {
	Screen    *string `json:"screen"`
	Client    *string `json:"client"`
	Version   *string `json:"version"`
	Mobile    *bool   `json:"mobile"`
	Os        *string `json:"os"`
	OsVersion *string `json:"os_version"`
	Cookies   *bool   `json:"cookies"`
}
