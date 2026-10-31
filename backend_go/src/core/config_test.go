package core

import (
	"net/url"
	"strings"
	"testing"
)

func TestNormalizeDSN(t *testing.T) {
	cases := []struct {
		name, dsn, user, password, host, path, sslmode string
	}{
		{"plain", "postgresql://u:p@db:5432/notifier", "u", "p", "db:5432", "/notifier", "disable"},
		{"sqlalchemy driver", "postgresql+psycopg2://u:p@db:5432/n", "u", "p", "db:5432", "/n", "disable"},
		{"hash in password", "postgresql://admin:VServ#vrt@postgres:5432/notifier", "admin", "VServ#vrt", "postgres:5432", "/notifier", "disable"},
		{"special characters", "postgresql://u:a@b/c?d:e@db:5432/n", "u", "a@b/c?d:e", "db:5432", "/n", "disable"},
		{"percent encoded", "postgresql://u:p%23w%40d@db/n", "u", "p#w@d", "db", "/n", "disable"},
		{"stray percent", "postgresql://u:50%off@db/n", "u", "50%off", "db", "/n", "disable"},
		{"explicit sslmode", "postgresql://u:p@db/n?sslmode=require", "u", "p", "db", "/n", "require"},
		{"postgres scheme", "postgres://u:p@db/n", "u", "p", "db", "/n", "disable"},
	}

	for _, c := range cases {
		parsed, err := url.Parse(normalizeDSN(c.dsn))
		if err != nil {
			t.Errorf("%s: result is not a valid url: %v", c.name, err)
			continue
		}

		password, _ := parsed.User.Password()
		if parsed.User.Username() != c.user || password != c.password || parsed.Host != c.host ||
			parsed.Path != c.path || parsed.Query().Get("sslmode") != c.sslmode {
			t.Errorf("%s: got %s / %s @ %s %s ?%s", c.name, parsed.User.Username(), password, parsed.Host, parsed.Path, parsed.RawQuery)
		}
	}
}

func TestRedactHidesPassword(t *testing.T) {
	Cfg.DatabaseURL = normalizeDSN("postgresql://admin:Secr#t@db/n")

	err := redact(&url.Error{Op: "parse", URL: "postgresql://admin:Secr#t@db/n", Err: errString("bad")})
	if got := err.Error(); strings.Contains(got, "Secr#t") || strings.Contains(got, "Secr%23t") {
		t.Errorf("password is shown: %s", got)
	}
}

type errString string

func (e errString) Error() string { return string(e) }
