package delivery_service

import (
	"context"
	"errors"
	"sync"
	"sync/atomic"
	"testing"
	"time"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

// The test needs a database: TEST_DATABASE_URL=postgresql://user:pass@host:port/db
func openTestDb(t *testing.T) {
	t.Helper()

	url := getenv("TEST_DATABASE_URL")
	if url == "" {
		t.Skip("TEST_DATABASE_URL is not set")
	}

	t.Setenv("SQLALCHEMY_URI", url)
	core.LoadConfig()
	if err := core.InitDBConnection(); err != nil {
		t.Fatal(err)
	}
	if err := core.Migrate(); err != nil {
		t.Fatal(err)
	}

	core.DB.Exec(`delete from delivery`)
	t.Cleanup(func() { core.DB.Exec(`delete from delivery`); core.DB.Close() })
}

type fakeHandler struct {
	deliver func(models.Delivery) error
}

func (h *fakeHandler) Deliver(d models.Delivery) error { return h.deliver(d) }
func (h *fakeHandler) Close()                          {}

func statuses(t *testing.T) map[string]int {
	t.Helper()

	rows, err := core.DB.Query(`select status, count(*) from delivery group by status`)
	if err != nil {
		t.Fatal(err)
	}
	defer rows.Close()

	result := map[string]int{}
	for rows.Next() {
		var status string
		var count int
		rows.Scan(&status, &count)
		result[status] = count
	}

	return result
}

func waitFor(t *testing.T, what string, condition func() bool) {
	t.Helper()

	for deadline := time.Now().Add(15 * time.Second); time.Now().Before(deadline); time.Sleep(50 * time.Millisecond) {
		if condition() {
			return
		}
	}

	t.Fatalf("timeout: %s (%v)", what, statuses(t))
}

func TestDispatcherDeliversRetriesAndFails(t *testing.T) {
	openTestDb(t)

	const total = 300
	var batch []models.Delivery
	for i := 0; i < total; i++ {
		account := "ok"
		switch {
		case i%10 == 1:
			account = "temporary" // fails once, then works
		case i%10 == 2:
			account = "refused" // never works
		}
		batch = append(batch, models.Delivery{NotifyId: i, UserId: 1, Type: "email", Account: account, Title: "t", Body: "b"})
	}
	// a message of another channel must not be touched by this dispatcher
	batch = append(batch, models.Delivery{Type: "tg", Account: "1"})

	if err := crud.InsertDeliveries(core.DB, batch, time.Now()); err != nil {
		t.Fatal(err)
	}

	var sent, inParallel, maxParallel atomic.Int64
	var seenTemporary sync.Map

	channel := Channel{Type: "email", Workers: 8, NewHandler: func() Handler {
		return &fakeHandler{deliver: func(d models.Delivery) error {
			now := inParallel.Add(1)
			defer inParallel.Add(-1)
			for {
				old := maxParallel.Load()
				if now <= old || maxParallel.CompareAndSwap(old, now) {
					break
				}
			}
			time.Sleep(5 * time.Millisecond)

			switch d.Account {
			case "refused":
				return permanent(errors.New("550 no such user"))
			case "temporary":
				if _, again := seenTemporary.LoadOrStore(d.NotifyId, true); !again {
					return errors.New("connection reset")
				}
			}

			sent.Add(1)
			return nil
		}}
	}}

	ctx, cancel := context.WithCancel(context.Background())
	wg := Start(ctx, []Channel{channel})

	// the first pass: everything except the temporary failures is finished
	waitFor(t, "first pass", func() bool {
		s := statuses(t)
		return s[crud.DeliveryProcess] == 0 && s[crud.DeliveryFailed] == 30 && s[crud.DeliveryNew] == 31 && s[crud.DeliveryDone] == total-60
	})
	cancel()
	wg.Wait()

	s := statuses(t)
	if s[crud.DeliveryFailed] != 30 {
		t.Errorf("refused messages must fail, got %v", s)
	}
	if s[crud.DeliveryNew] != 30+1 {
		t.Errorf("30 temporary failures and the telegram message must wait in the queue, got %v", s)
	}
	if maxParallel.Load() < 2 {
		t.Errorf("messages were delivered one by one, parallelism %d", maxParallel.Load())
	}

	// the retry is due in 10 seconds: make it due now and run again
	core.DB.Exec(`update delivery set next_attempt_at = now() at time zone 'utc' where status = 'new' and system_type = 'email'`)

	ctx, cancel = context.WithCancel(context.Background())
	wg = Start(ctx, []Channel{channel})
	waitFor(t, "retry pass", func() bool { return statuses(t)[crud.DeliveryNew] == 1 })
	cancel()
	wg.Wait()

	if s = statuses(t); s[crud.DeliveryDone] != total-30 || s[crud.DeliveryFailed] != 30 {
		t.Errorf("final statuses: %v", s)
	}
}

func TestResetStuck(t *testing.T) {
	openTestDb(t)

	long := time.Now().Add(-10 * time.Minute)

	if err := crud.InsertDeliveries(core.DB, []models.Delivery{{Type: "email", Account: "a"}}, long.Add(-time.Minute)); err != nil {
		t.Fatal(err)
	}

	// a dispatcher took the message and died
	if claimed, err := crud.ClaimDeliveries(core.DB, "email", 10, long); err != nil || len(claimed) != 1 {
		t.Fatalf("claim: %v %v", claimed, err)
	}

	if claimed, _ := crud.ClaimDeliveries(core.DB, "email", 10, time.Now()); len(claimed) != 0 {
		t.Fatal("a taken message was given twice")
	}

	if err := ResetStuck(); err != nil {
		t.Fatal(err)
	}
	if claimed, _ := crud.ClaimDeliveries(core.DB, "email", 10, time.Now()); len(claimed) != 1 {
		t.Fatal("a stuck message was not returned to the queue")
	}
}
