package date_operation_service

import (
	"testing"
	"time"
)

func TestDateData(t *testing.T) {
	cases := []struct {
		date                           time.Time
		day, dayOfWeek, month, lastDay int
		clock, formattedDate           string
	}{
		// sunday is 7, monday is 1
		{time.Date(2026, 11, 1, 10, 5, 0, 0, time.UTC), 1, 7, 11, 30, "10:05", "2026-11-01"},
		{time.Date(2026, 11, 2, 0, 0, 0, 0, time.UTC), 2, 1, 11, 30, "00:00", "2026-11-02"},
		{time.Date(2024, 2, 10, 23, 59, 0, 0, time.UTC), 10, 6, 2, 29, "23:59", "2024-02-10"},
		{time.Date(2025, 2, 28, 12, 0, 0, 0, time.UTC), 28, 5, 2, 28, "12:00", "2025-02-28"},
		{time.Date(2025, 12, 31, 12, 0, 0, 0, time.UTC), 31, 3, 12, 31, "12:00", "2025-12-31"},
	}

	for _, c := range cases {
		got := DateData(c.date)

		if got.FirstDay != 1 || got.LastDay != c.lastDay || got.Day != c.day || got.DayOfWeek != c.dayOfWeek ||
			got.Month != c.month || got.Time != c.clock || got.Date != c.formattedDate {
			t.Errorf("%s: %+v", c.date, got)
		}
	}
}

func TestMinutesBetween(t *testing.T) {
	first := time.Date(2026, 12, 31, 23, 58, 0, 0, time.UTC)
	last := time.Date(2027, 1, 1, 0, 1, 0, 0, time.UTC)

	got := minutesBetween(first, last)

	want := []string{"23:58", "23:59", "00:00", "00:01"}
	if len(got) != len(want) {
		t.Fatalf("got %d minutes, want %d", len(got), len(want))
	}
	for i, minute := range got {
		if minute.Time != want[i] {
			t.Errorf("minute %d: %s, want %s", i, minute.Time, want[i])
		}
	}
	if got[2].Date != "2027-01-01" || got[2].Month != 1 || got[1].Date != "2026-12-31" {
		t.Errorf("the date does not change at midnight: %+v", got)
	}

	if single := minutesBetween(first, first); len(single) != 1 {
		t.Errorf("a single minute: %d", len(single))
	}
}
