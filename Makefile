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

migrate:  ## Обновление БД проекта
	@docker-compose -f docker/docker-compose.yml run backend \
			sh -c "alembic `([ ! -z "$(downgrade)" ] && echo "downgrade -$(downgrade)") || \
  		([ ! -z "$(upgrade)" ] && echo "upgrade $(upgrade)") || \
			echo "upgrade head"`"

migration_create:  ## Создание новой миграции
	@[ -z "$(msg)" ] && \
		echo -e '${Red}Добавьте msg${Color_Off}' || \
	  docker-compose -f docker/docker-compose.yml run backend sh -c "alembic revision --autogenerate -m '$(msg)'"
	@sudo chown $$USER:$$USER -R $(DIR)/backend/app/repo/migrations/versions


ps:
	@docker-compose -f docker/docker-compose.yml ps

publish:  ## Сборка проекта
	@docker build --no-cache -f ./docker/backend/Dockerfile --target prod -t harbor.nic-weiss.tech/notifier/backend:$(VERSION) .
	@docker build --no-cache -f ./docker/backend/Dockerfile --target celery -t harbor.nic-weiss.tech/notifier/celery:$(VERSION) .
	@docker build --no-cache -f ./docker/frontend/Dockerfile --target prod -t harbor.nic-weiss.tech/notifier/frontend:$(VERSION) .
	@docker build --no-cache -f ./docker/postfix/Dockerfile -t harbor.nic-weiss.tech/notifier/postfix:$(VERSION) .

	@docker push harbor.nic-weiss.tech/notifier/frontend:$(VERSION)
	@docker push harbor.nic-weiss.tech/notifier/backend:$(VERSION)
	@docker push harbor.nic-weiss.tech/notifier/celery:$(VERSION)
	@docker push harbor.nic-weiss.tech/notifier/postfix:$(VERSION)
