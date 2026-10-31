// Package delivery_service delivers the messages of the outbox (table
// delivery). Every channel (email, telegram, push) has its own claimer, pool of
// workers and result collector, so a slow channel never blocks the others.
//
//	claimer --jobs--> workers (N) --results--> collector --> batch update
//
// The claimer takes only as many messages as the workers can accept, the
// collector writes the outcomes of many messages by one statement.
package delivery_service

import (
	"context"
	"fmt"
	"log"
	"sync"
	"time"

	"notifier/src/core"
	"notifier/src/repo/crud"
	"notifier/src/repo/models"
)

var backgroundContext = context.Background()

const (
	pollEvery      = time.Second
	flushEvery     = 200 * time.Millisecond
	flushBatchSize = 200
	stuckAfter     = 2 * time.Minute

	doneKeep   = 24 * time.Hour
	failedKeep = 7 * 24 * time.Hour
)

type result struct {
	delivery models.Delivery
	err      error
}

type runner struct {
	channel Channel
	wake    chan struct{}
}

var (
	runnersMutex sync.Mutex
	runners      []*runner
)

// Nudge makes the dispatchers look into the queue right now, without waiting
// for the next poll.
func Nudge() {
	runnersMutex.Lock()
	defer runnersMutex.Unlock()

	for _, r := range runners {
		select {
		case r.wake <- struct{}{}:
		default:
		}
	}
}

// Start runs the dispatchers until ctx is cancelled. The returned group
// is done when all taken messages have been delivered and saved.
func Start(ctx context.Context, channels []Channel) *sync.WaitGroup {
	var wg sync.WaitGroup

	runnersMutex.Lock()
	runners = runners[:0]
	for _, channel := range channels {
		r := &runner{channel: channel, wake: make(chan struct{}, 1)}
		runners = append(runners, r)

		wg.Add(1)
		go func() {
			defer wg.Done()
			r.run(ctx)
		}()
	}
	runnersMutex.Unlock()

	return &wg
}

func (r *runner) run(ctx context.Context) {
	workers := max(r.channel.Workers, 1)

	jobs := make(chan models.Delivery, max(workers*8, 64))
	results := make(chan result, cap(jobs))

	var workerGroup sync.WaitGroup
	for i := 0; i < workers; i++ {
		workerGroup.Add(1)
		go func() {
			defer workerGroup.Done()

			handler := r.channel.NewHandler()
			defer handler.Close()

			for delivery := range jobs {
				results <- result{delivery: delivery, err: safeDeliver(handler, delivery)}
			}
		}()
	}

	collected := make(chan struct{})
	go func() {
		defer close(collected)
		r.collect(results)
	}()

	r.claim(ctx, jobs)

	// shutdown: finish what is already taken
	close(jobs)
	workerGroup.Wait()
	close(results)
	<-collected
}

// claim takes messages from the queue while the workers have a free place.
func (r *runner) claim(ctx context.Context, jobs chan<- models.Delivery) {
	ticker := time.NewTicker(pollEvery)
	defer ticker.Stop()

	for {
		for ctx.Err() == nil {
			free := cap(jobs) - len(jobs)
			if free <= 0 {
				break
			}

			batch, err := crud.ClaimDeliveries(core.DB, r.channel.Type, free, time.Now())
			if err != nil {
				log.Printf("%s: can't take messages: %v", r.channel.Type, err)
				break
			}

			for _, delivery := range batch {
				jobs <- delivery // never blocks: only free places were requested
			}

			if len(batch) < free {
				break
			}
		}

		select {
		case <-ctx.Done():
			return
		case <-r.wake:
		case <-ticker.C:
		}
	}
}

func safeDeliver(handler Handler, delivery models.Delivery) (err error) {
	defer func() {
		if p := recover(); p != nil {
			err = &panicError{value: p}
		}
	}()

	return handler.Deliver(delivery)
}

type panicError struct{ value any }

func (e *panicError) Error() string { return "panic: " + fmt.Sprint(e.value) }

// collect saves outcomes in batches: by size or by time.
func (r *runner) collect(results <-chan result) {
	ticker := time.NewTicker(flushEvery)
	defer ticker.Stop()

	buffer := make([]result, 0, flushBatchSize)

	for {
		select {
		case item, ok := <-results:
			if !ok {
				r.flush(buffer)
				return
			}

			buffer = append(buffer, item)
			if len(buffer) >= flushBatchSize {
				r.flush(buffer)
				buffer = buffer[:0]
			}

		case <-ticker.C:
			if len(buffer) > 0 {
				r.flush(buffer)
				buffer = buffer[:0]
			}
		}
	}
}

func (r *runner) flush(items []result) {
	if len(items) == 0 {
		return
	}

	var (
		doneIds               []int64
		retryIds, retryDelays []int64
		retryReasons          []string
		failedIds             []int64
		failedReasons         []string
	)

	for _, item := range items {
		decision := decide(item.delivery, item.err)

		switch decision.status {
		case crud.DeliveryDone:
			doneIds = append(doneIds, item.delivery.Id)

		case crud.DeliveryNew:
			retryIds = append(retryIds, item.delivery.Id)
			retryDelays = append(retryDelays, int64(decision.delay/time.Second))
			retryReasons = append(retryReasons, decision.reason)

		default:
			failedIds = append(failedIds, item.delivery.Id)
			failedReasons = append(failedReasons, decision.reason)
			if len(failedIds) <= 3 {
				log.Printf("%s: message %d (notify %d) is failed: %s",
					r.channel.Type, item.delivery.Id, item.delivery.NotifyId, decision.reason)
			}
		}
	}

	// the statuses must not be lost: the database may blink
	saved := false
	for attempt := 1; attempt <= 5 && !saved; attempt++ {
		now := time.Now()

		err := crud.MarkDeliveriesDone(core.DB, doneIds, now)
		if err == nil {
			err = crud.RetryDeliveries(core.DB, retryIds, retryDelays, retryReasons, now)
		}
		if err == nil {
			err = crud.FailDeliveries(core.DB, failedIds, failedReasons, now)
		}

		if saved = err == nil; !saved {
			log.Printf("%s: can't save %d results (attempt %d): %v", r.channel.Type, len(items), attempt, err)
			time.Sleep(time.Duration(attempt) * time.Second)
		}
	}

	// one line per batch, not per message: an outage of a server would flood the log
	if len(retryIds) > 0 {
		log.Printf("%s: %d messages will be retried (for example: %s)", r.channel.Type, len(retryIds), retryReasons[0])
	}
	if len(failedIds) > 3 {
		log.Printf("%s: %d messages are failed in total", r.channel.Type, len(failedIds))
	}
	if len(doneIds) > 0 {
		log.Printf("%s: %d sent", r.channel.Type, len(doneIds))
	}
}

// ResetStuck returns messages of a dead dispatcher to the queue.
func ResetStuck() error {
	count, err := crud.ResetStuckDeliveries(core.DB, time.Now().Add(-stuckAfter))
	if err == nil && count > 0 {
		log.Printf("%d stuck messages returned to the queue", count)
	}

	return err
}

// Cleanup removes old finished messages.
func Cleanup() error {
	now := time.Now()
	return crud.ClearDeliveries(core.DB, now.Add(-doneKeep), now.Add(-failedKeep))
}
