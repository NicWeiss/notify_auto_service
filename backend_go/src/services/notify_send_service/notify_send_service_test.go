package notify_send_service

import (
	"testing"
	"time"

	"notifier/src/repo/models"
)

func TestNextTime(t *testing.T) {
	cases := []struct {
		current  string
		interval time.Duration
		want     string
	}{
		{"10:00", 90 * time.Minute, "11:30"},
		{"23:50", 20 * time.Minute, "00:10"},
		{"10:00", 15 * time.Hour, "01:00"},
		{"00:00", 24 * time.Hour, "00:00"},
	}

	for _, c := range cases {
		got, err := nextTime(c.current, c.interval)
		if err != nil || got != c.want {
			t.Errorf("%s + %s = %s (%v), want %s", c.current, c.interval, got, err, c.want)
		}
	}

	if _, err := nextTime("broken", time.Minute); err == nil {
		t.Error("broken time accepted")
	}
}

func intPtr(v int) *int { return &v }

func TestBuildPlan(t *testing.T) {
	now := time.Date(2026, 11, 10, 10, 0, 0, 0, time.UTC)
	past := now.Add(-time.Hour)
	text := "body"

	email := models.Acceptor{SystemType: "email", Account: "a@example.com"}
	push := models.Acceptor{SystemType: "push", Account: "t1;;t2;"}
	telegram := models.Acceptor{SystemType: "tg", Account: "42"}
	empty := models.Acceptor{SystemType: "email", Account: ""}

	notifies := []models.Notify{
		{Id: 1, UserId: 7, Name: "every day", Text: &text, Periodic: "everyday", Time: "10:00", Acceptors: []models.Acceptor{email, push, telegram, empty}},
		{Id: 2, Name: "no acceptors", Periodic: "everyday", Time: "10:00"},
		{Id: 3, Name: "interval", Periodic: "every_several_minutes", Time: "10:00", RepeateInterval: intPtr(90), Acceptors: []models.Acceptor{email}},
		{Id: 4, Name: "expired", Periodic: "everyday", Time: "10:00", IsAutodisable: true, AutodisableAt: &past, Acceptors: []models.Acceptor{email}},
	}

	got := buildPlan(notifies, now)

	// notify 1: email + 2 push tokens + telegram, notify 3 and 4: email
	if len(got.deliveries) != 4+1+1 {
		t.Fatalf("deliveries: %d: %+v", len(got.deliveries), got.deliveries)
	}

	first := got.deliveries[0]
	if first.NotifyId != 1 || first.UserId != 7 || first.Type != "email" || first.Account != "a@example.com" ||
		first.Title != "every day" || first.Body != "body" {
		t.Errorf("first delivery: %+v", first)
	}
	if got.deliveries[1].Account != "t1" || got.deliveries[2].Account != "t2" {
		t.Errorf("push tokens are not split: %+v", got.deliveries[1:3])
	}

	if len(got.nextTimeIds) != 1 || got.nextTimeIds[0] != 3 || got.nextTimes[0] != "11:30" {
		t.Errorf("next times: %v %v", got.nextTimeIds, got.nextTimes)
	}
	if len(got.disableIds) != 1 || got.disableIds[0] != 4 {
		t.Errorf("disable: %v", got.disableIds)
	}
}
