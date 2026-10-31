package main

import (
	"context"
	"errors"
	"log"
	"net"
	"net/http"
	"os/signal"
	"syscall"
	"time"

	"github.com/gin-gonic/gin"

	"notifier/src/api"
	"notifier/src/core"
	"notifier/src/tasks"
)

// @title       Notifier
// @version     3.0
// @BasePath    /api/v3
func main() {
	core.LoadConfig()

	if !core.Cfg.IsDebug {
		gin.SetMode(gin.ReleaseMode)
	}

	if err := core.InitDBConnection(); err != nil {
		log.Fatal(err)
	}
	if err := core.Migrate(); err != nil {
		log.Fatalf("can't migrate database: %v", err)
	}

	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGINT, syscall.SIGTERM)
	defer stop()

	var workers interface{ Wait() }
	if core.Cfg.DoNotRunPeriodicTask {
		log.Println("Periodic tasks are disabled")
	} else {
		workers = tasks.Start(ctx)
	}

	router := gin.New()
	router.Use(gin.Logger(), gin.Recovery())
	api.InitApi(router)

	server := &http.Server{
		Addr:              net.JoinHostPort(core.Cfg.AppHost, core.Cfg.AppPort),
		Handler:           router,
		ReadHeaderTimeout: 10 * time.Second,
	}

	go func() {
		<-ctx.Done()

		shutdownCtx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
		defer cancel()
		server.Shutdown(shutdownCtx)
	}()

	log.Printf("Listening on %s", server.Addr)
	if err := server.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
		log.Fatal(err)
	}

	if workers != nil {
		workers.Wait()
	}
	core.DB.Close()
}
