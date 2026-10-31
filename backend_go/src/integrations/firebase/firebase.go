package firebase

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	"net/http"
	"time"
)

var httpClient = &http.Client{Timeout: 10 * time.Second}

// ErrInvalidToken means that FCM does not know the device token any more, so
// it can be forgotten. Any other error is temporary or a configuration one.
// ErrNotConfigured: there is no server key.
var ErrNotConfigured = errors.New("FCM_SERVER_KEY is not configured")

var ErrInvalidToken = errors.New("fcm token is not valid")

// SendPush uses the legacy FCM HTTP api, like the python backend did.
// NOTE: Google has switched this api off; moving to HTTP v1 needs a service
// account instead of FCM_SERVER_KEY.
func SendPush(serverKey string, token string, title string, text string) error {
	if serverKey == "" {
		return ErrNotConfigured
	}

	payload, err := json.Marshal(map[string]any{
		"to": token,
		"notification": map[string]string{
			"title": title,
			"body":  text,
			"icon":  "ic_launcher",
			"color": "#de6c20",
		},
	})
	if err != nil {
		return err
	}

	request, err := http.NewRequest(http.MethodPost, "https://fcm.googleapis.com/fcm/send", bytes.NewReader(payload))
	if err != nil {
		return err
	}
	request.Header.Set("Content-Type", "application/json")
	request.Header.Set("Authorization", "key="+serverKey)

	response, err := httpClient.Do(request)
	if err != nil {
		return err
	}
	defer response.Body.Close()

	if response.StatusCode != http.StatusOK {
		return fmt.Errorf("fcm answered %d", response.StatusCode)
	}

	// the legacy api answers 200 even if the message was not delivered
	var result struct {
		Failure int `json:"failure"`
		Results []struct {
			Error string `json:"error"`
		} `json:"results"`
	}
	if json.NewDecoder(response.Body).Decode(&result) != nil || result.Failure == 0 || len(result.Results) == 0 {
		return nil
	}

	switch code := result.Results[0].Error; code {
	case "NotRegistered", "InvalidRegistration", "MismatchSenderId":
		return fmt.Errorf("%w: %s", ErrInvalidToken, code)
	default:
		return fmt.Errorf("fcm error: %s", code)
	}
}
