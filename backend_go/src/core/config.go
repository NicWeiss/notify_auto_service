package core

import (
	"bufio"
	"net/url"
	"os"
	"strconv"
	"strings"
)

// Config holds the service settings. Variable names are the same as in the
// former Python backend, so an existing .env keeps working.
type Config struct {
	AppHost string
	AppPort string

	DatabaseURL string

	SMTPURI     string
	SenderEmail string
	SenderName  string

	IPLocationToken string
	TelegramToken   string
	FCMServerKey    string

	// PublicURL (e.g. https://notifier.example.com) is used to build the
	// password restore link. When empty the link is built from request headers.
	PublicURL string

	DoNotRunPeriodicTask bool
	IsDebug              bool

	// Limit of the minutes which are checked after a downtime of the service
	// (the older ones are lost). 0 - no limit: everything missed is sent.
	CatchUpMinutes int

	// Parallel senders of every channel and the Telegram limit (messages per second).
	EmailWorkers    int
	TelegramWorkers int
	PushWorkers     int
	TelegramRate    float64
}

var Cfg Config

func LoadConfig() {
	loadDotEnv(".env")

	Cfg = Config{
		AppHost: env("APP_HOST", "0.0.0.0"),
		AppPort: env("APP_PORT", "8800"),

		DatabaseURL: normalizeDSN(env("SQLALCHEMY_URI", "postgresql://test:test@localhost:8097/test")),

		SMTPURI:     env("SMTP_URI", ""),
		SenderEmail: env("NOTIFIER_SENDER_EMAIL", ""),
		SenderName:  env("NOTIFIER_SENDER_NAME", "Notifier"),

		IPLocationToken: env("IP_LOCATION_PROVIDER_TOKEN", ""),
		TelegramToken:   env("TELERGAM_BOT_TOKEN", ""),
		FCMServerKey:    env("FCM_SERVER_KEY", ""),

		PublicURL: strings.TrimRight(env("PUBLIC_URL", ""), "/"),

		DoNotRunPeriodicTask: envBool("DO_NOT_RUN_PERIODIC_TASK", false),
		IsDebug:              envBool("IS_DEBUG", false),

		CatchUpMinutes:  envInt("CATCH_UP_MINUTES", 0),
		EmailWorkers:    envInt("DELIVERY_EMAIL_WORKERS", 16),
		TelegramWorkers: envInt("DELIVERY_TELEGRAM_WORKERS", 8),
		PushWorkers:     envInt("DELIVERY_PUSH_WORKERS", 16),
		TelegramRate:    float64(envInt("DELIVERY_TELEGRAM_RATE", 25)),
	}
}

func env(key string, fallback string) string {
	for _, k := range []string{key, strings.ToLower(key)} {
		if value, ok := os.LookupEnv(k); ok && value != "" {
			return value
		}
	}

	return fallback
}

func envBool(key string, fallback bool) bool {
	value, err := strconv.ParseBool(env(key, ""))
	if err != nil {
		return fallback
	}

	return value
}

func envInt(key string, fallback int) int {
	value, err := strconv.Atoi(env(key, ""))
	if err != nil || value < 1 {
		return fallback
	}

	return value
}

// normalizeDSN converts SQLAlchemy style URIs (postgresql+psycopg2://...) into
// something lib/pq understands. Without an explicit sslmode lib/pq would
// require TLS, while psycopg2 (used before) silently fell back to plain text.
//
// The password is allowed to contain characters like '#', '@', '/' or '?'
// without percent-encoding (the python backend accepted it, and the existing
// .env files rely on it), so the string is not given to url.Parse as is:
// the credentials are cut off by the last '@' and encoded afterwards.
func normalizeDSN(dsn string) string {
	scheme, rest, found := strings.Cut(dsn, "://")
	if !found {
		return dsn
	}
	if i := strings.Index(scheme, "+"); i >= 0 {
		scheme = scheme[:i]
	}

	result := &url.URL{Scheme: scheme}

	if at := strings.LastIndex(rest, "@"); at >= 0 {
		name, password, hasPassword := strings.Cut(rest[:at], ":")
		name, password = unescapeIfEncoded(name), unescapeIfEncoded(password)

		if hasPassword {
			result.User = url.UserPassword(name, password)
		} else {
			result.User = url.User(name)
		}
		rest = rest[at+1:]
	}

	location, err := url.Parse("//" + rest)
	if err != nil {
		// do not show the string: it contains the password
		return dsn
	}
	result.Host, result.Path = location.Host, location.Path

	query := location.Query()
	if query.Get("sslmode") == "" {
		query.Set("sslmode", "disable")
	}
	result.RawQuery = query.Encode()

	return result.String()
}

// unescapeIfEncoded decodes %XX sequences; a value with a stray '%' is
// returned as it is.
func unescapeIfEncoded(value string) string {
	if decoded, err := url.PathUnescape(value); err == nil {
		return decoded
	}

	return value
}

// loadDotEnv reads KEY=VALUE lines. Variables already present in the
// environment are not overridden.
func loadDotEnv(path string) {
	file, err := os.Open(path)
	if err != nil {
		return
	}
	defer file.Close()

	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		line := strings.TrimSpace(scanner.Text())
		if line == "" || strings.HasPrefix(line, "#") {
			continue
		}

		key, value, found := strings.Cut(line, "=")
		if !found {
			continue
		}

		key = strings.TrimSpace(key)
		value = strings.Trim(strings.TrimSpace(value), `"'`)

		if _, exists := os.LookupEnv(key); !exists {
			os.Setenv(key, value)
		}
	}
}
