package main

import (
	"flag"
	"fmt"
	"log"
	"os"
	"time"

	"notifier/src/core"
	"notifier/src/services/date_operation_service"
	"notifier/src/services/notify_send_service"
)

const minuteLayout = "2006-01-02 15:04"

// runBackfill sends the notifies of the minutes which were never checked
// (for example the time of a long downtime before the catch up was fixed).
//
//	notifier backfill -from "2026-05-07 07:00" -to "2026-10-30 19:00"          counts only
//	notifier backfill -from "2026-05-07 07:00" -to "2026-10-30 19:00" -apply   creates the operations
//
// The time is UTC, as in the date_operation table.
func runBackfill(args []string) {
	flags := flag.NewFlagSet("backfill", flag.ExitOnError)
	fromText := flags.String("from", "", `first minute, UTC, "2006-01-02 15:04"`)
	toText := flags.String("to", "", `last minute, UTC, "2006-01-02 15:04"`)
	apply := flags.Bool("apply", false, "really create the operations (without it the messages are only counted)")
	flags.Parse(args)

	from, errFrom := time.ParseInLocation(minuteLayout, *fromText, time.UTC)
	to, errTo := time.ParseInLocation(minuteLayout, *toText, time.UTC)
	if errFrom != nil || errTo != nil || to.Before(from) {
		fmt.Fprintln(os.Stderr, `usage: notifier backfill -from "2026-05-07 07:00" -to "2026-10-30 19:00" [-apply]`)
		os.Exit(2)
	}

	core.LoadConfig()
	if err := core.InitDBConnection(); err != nil {
		log.Fatal(err)
	}
	defer core.DB.Close()

	fmt.Printf("Period: %s - %s UTC (%d minutes)\n", from.Format(minuteLayout), to.Format(minuteLayout), int(to.Sub(from)/time.Minute)+1)

	if !*apply {
		started := time.Now()
		report, err := notify_send_service.DryRun(from, to)
		if err != nil {
			log.Fatal(err)
		}

		fmt.Printf("DRY RUN (nothing is changed or sent, %s): %d messages\n", time.Since(started).Round(time.Second), report.Messages)
		for systemType, count := range report.ByType {
			fmt.Printf("  %-6s %d\n", systemType, count)
		}
		for i, entry := range report.ByNotify {
			if i == 20 {
				fmt.Printf("  ... and %d more notifies\n", len(report.ByNotify)-i)
				break
			}
			fmt.Printf("  notify %-5d %-6d %s\n", entry.Id, entry.Count, entry.Name)
		}
		fmt.Println("Add -apply to send them.")
		return
	}

	created, err := date_operation_service.Backfill(from, to)
	if err != nil {
		log.Fatal(err)
	}
	fmt.Printf("%d operations are created, the running service will queue and send the messages\n", created)
}
