// Package tasks runs the background jobs of the notifier (formerly celery tasks):
//
//	time watcher  -> creates an operation for every new minute
//	matcher       -> turns operations into queued messages (outbox)
//	dispatchers   -> deliver the queued messages (see delivery_service)
//	housekeeping  -> returns stuck work to the queue, removes old records
package tasks

import (
	"context"
	"log"
	"sync"
	"time"

	"notifier/src/services/auth_service"
	"notifier/src/services/date_operation_service"
	"notifier/src/services/delivery_service"
	"notifier/src/services/notify_send_service"
	"notifier/src/services/session_service"
)

// Start launches all jobs. They stop when ctx is cancelled; wait for the
// returned WaitGroup to be sure that nothing is running any more.
func Start(ctx context.Context) *sync.WaitGroup {
	var wg sync.WaitGroup

	matcherWake := make(chan struct{}, 1)
	wakeMatcher := func() {
		select {
		case matcherWake <- struct{}{}:
		default:
		}
	}

	run := func(name string, every time.Duration, wake <-chan struct{}, job func() error) {
		wg.Add(1)
		go func() {
			defer wg.Done()
			loop(ctx, name, every, wake, job)
		}()
	}

	run("time watcher", time.Second, nil, func() error {
		created, err := date_operation_service.CreateNewOperations(time.Now())
		if created > 0 {
			wakeMatcher()
		}
		return err
	})

	run("matcher", time.Second, matcherWake, func() error {
		queued, err := notify_send_service.ProcessPending()
		if queued > 0 {
			delivery_service.Nudge()
		}
		return err
	})

	run("stuck work check", time.Minute, nil, func() error {
		if err := date_operation_service.ResetStuckOperations(); err != nil {
			return err
		}
		return delivery_service.ResetStuck()
	})

	run("db clear", 5*time.Minute, nil, clearDb)

	wg.Add(1)
	dispatchers := delivery_service.Start(ctx, delivery_service.DefaultChannels())
	go func() {
		defer wg.Done()
		dispatchers.Wait()
	}()

	return &wg
}

func loop(ctx context.Context, name string, every time.Duration, wake <-chan struct{}, job func() error) {
	ticker := time.NewTicker(every)
	defer ticker.Stop()

	for {
		safeRun(name, job)

		select {
		case <-ctx.Done():
			return
		case <-wake:
		case <-ticker.C:
		}
	}
}

func safeRun(name string, job func() error) {
	defer func() {
		if p := recover(); p != nil {
			log.Printf("Task %s panic: %v", name, p)
		}
	}()

	if err := job(); err != nil {
		log.Printf("Task %s error: %v", name, err)
	}
}

func clearDb() error {
	if err := date_operation_service.ClearDoneOperations(); err != nil {
		return err
	}
	if err := delivery_service.Cleanup(); err != nil {
		return err
	}
	if err := session_service.ClearExpiredSessions(); err != nil {
		return err
	}

	return auth_service.ClearExpiredCodes()
}
