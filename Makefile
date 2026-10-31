SHELL=/bin/sh

DIR=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
VERSION=$(shell grep -o '^[a-z_0-9]\+\.[0-9]\+\.[0-9_a-z]\+' CHANGES.log | head -n1)

# Colors
Color_Off=\033[0m
Cyan=\033[1;36m
Red=\033[1;31m

version:  ## Версия проекта
	@echo $(VERSION)

help:  ## Помощь
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

### Development
build:  ## Сборка проекта
	@docker-compose -f docker/docker-compose.yml build

start: ## Запуск проекта для разработки
	@docker-compose -f docker/docker-compose.yml up

stop: ## Остановка проекта
	@docker-compose -f docker/docker-compose.yml down

# Миграции применяются автоматически при старте бэкэнда.
# Новая миграция - файл backend_go/src/core/migrations/NNN_name.sql
migration_create:  ## Создание новой миграции (make migration_create msg=add_field)
	@[ -z "$(msg)" ] && \
		echo -e '${Red}Добавьте msg${Color_Off}' || \
		(n=$$(ls $(DIR)/backend_go/src/core/migrations | wc -l); \
		f=$(DIR)/backend_go/src/core/migrations/$$(printf '%03d' $$((n + 1)))_$(msg).sql; \
		touch $$f && echo $$f)

test:  ## Тесты бэкэнда
	@docker run --rm -v $(DIR)/backend_go:/app -w /app golang:1.23-alpine go test ./...


ps:
	@docker-compose -f docker/docker-compose.yml ps

publish:  ## Сборка проекта
	@docker build --no-cache -f ./docker/backend/Dockerfile --target prod -t harbor.nic-weiss.tech/notifier/backend:$(VERSION) .
	@docker build --no-cache -f ./docker/frontend/Dockerfile --target prod -t harbor.nic-weiss.tech/notifier/frontend:$(VERSION) .

	@docker push harbor.nic-weiss.tech/notifier/frontend:$(VERSION)
	@docker push harbor.nic-weiss.tech/notifier/backend:$(VERSION)

publish_postfix:  ## Сборка проекта
	@docker build -f ./docker/postfix/Dockerfile -t harbor.nic-weiss.tech/notifier/postfix:$(VERSION) .
	@docker push harbor.nic-weiss.tech/notifier/postfix:$(VERSION)
