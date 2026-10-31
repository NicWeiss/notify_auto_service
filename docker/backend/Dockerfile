FROM golang:1.23-alpine AS build

WORKDIR /src
COPY ./backend_go/go.mod ./backend_go/go.sum ./
RUN go mod download

COPY ./backend_go/ ./
RUN CGO_ENABLED=0 go build -trimpath -ldflags="-s -w" -o /out/notifier ./src

# ---- PROD build ----
# One binary serves the api and runs the periodic jobs (the former celery worker).
FROM alpine:3.20 AS prod

RUN apk add --no-cache ca-certificates tzdata
COPY --from=build /out/notifier /usr/local/bin/notifier

EXPOSE 8800
CMD ["notifier"]
