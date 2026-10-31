package location

import (
	"encoding/json"
	"fmt"
	"net/http"
	"net/url"
	"time"
)

var httpClient = &http.Client{Timeout: 5 * time.Second}

type Location struct {
	Ip            string  `json:"ip"`
	City          *string `json:"city"`
	Region        *string `json:"region"`
	CountryName   *string `json:"country_name"`
	ContinentName *string `json:"continent_name"`
	Message       *string `json:"message"`
}

// Lookup asks ipdata.co for the place of ip. Without a token, or when the
// service fails, only the ip is returned.
func Lookup(token string, ip string) json.RawMessage {
	result := Location{Ip: ip}

	if token != "" && ip != "" {
		requestUrl := fmt.Sprintf(
			"https://api.ipdata.co/%s?api-key=%s&fields=ip,city,region,country_name,continent_name",
			url.PathEscape(ip), url.QueryEscape(token),
		)

		if response, err := httpClient.Get(requestUrl); err == nil {
			var found Location
			if json.NewDecoder(response.Body).Decode(&found) == nil {
				result = found
				if result.Ip == "" {
					result.Ip = ip
				}
			}
			response.Body.Close()
		}
	}

	raw, _ := json.Marshal(result)
	return raw
}
