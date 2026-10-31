#!/bin/bash
# Local run with live reload. Settings are read from backend_go/.env (see .env.example).
cd "$(dirname "$0")"
go run github.com/swaggo/swag/cmd/swag@v1.16.4 init -g main.go -d src -o src/docs
go run github.com/air-verse/air@v1.61.5
