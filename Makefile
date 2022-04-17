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
	@docker-compose -f docker/docker-compose.yml

publish:  ## Сборка проекта
	@docker build --no-cache -f ./docker/backend/Dockerfile -t harbor.nic-weiss.tech/notifier/backend:$(VERSION) .
	@docker build --no-cache -f ./docker/frontend/Dockerfile --target prod -t harbor.nic-weiss.tech/notifier/frontend:$(VERSION) .
	@docker build --no-cache -f ./docker/ingress/Dockerfile --target prod -t harbor.nic-weiss.tech/notifier/ingress:latest .
	@docker push harbor.nic-weiss.tech/notifier/frontend:$(VERSION)
	@docker push harbor.nic-weiss.tech/notifier/backend:$(VERSION)
	@docker push harbor.nic-weiss.tech/notifier/ingress:latest

start: ## Запуск проекта для разработки
	@docker-compose -f docker/docker-compose.yml up

stop: ## Остановка проекта
	@docker-compose -f docker/docker-compose.yml down

test: ## Запуск тестирования
	@echo VERSION: $(VERSION)

production:  ## Запуск проекта
	@echo VERSION: $(VERSION)
	@export VERSION=$(VERSION) && \
	docker-compose -f docker/docker-compose-production.yml --project-name="prod_" pull
	docker-compose -f docker/docker-compose-production.yml --project-name="prod_" up -d

down_production: ## Останов продакшена
	@echo VERSION: $(VERSION)
	@export VERSION=$(VERSION) && \
	docker-compose -f docker/docker-compose-production.yml --project-name="prod_" down

migration:  ## Создание новой миграции
	@docker-compose -f docker/docker-compose.yml run --user www-data backend sh -c "cd /var/www && php migration.php $(filter-out $@,$(MAKECMDGOALS))"
	@sudo chown $$USER:$$USER -R $(DIR)/api/app/migration/

stat:			# params for migration: Показать доступные миграции без применения.
	exit
init:			# params for migration: Инициализация таблицы миграций
	exit
create: 	# params for migration: Создать миграцию
	exit
up:				# params for migration: [count] Применить миграции (применит все доступные миграции);
	exit
down: 		# params for migration: [count|migration_id] Откатить миграции (default count=1);
	exit                         -
resolve:	# params for migration: Попытаться найти и откатить миграцию которая была удалена с диска, но есть базе
	exit

migrate_prod:
	@export VERSION=$(VERSION) && docker-compose -f docker/docker-compose-production.yml run --user www-data backend sh -c "cd /var/www && php migration.php up"

dump_restore:  ## Восстановление базы из бэкапа
	docker-compose run mysql bash -c "cd mysql && mysql -h mysql -u root -pmysql notifier < dump.sql"

dump_create:  ## Создание бекапа базы
	docker-compose run mysql bash -c "cd mysql && mysqldump -h mysql -u root -pmysql notifier > dump.sql"

ps:
	@docker-compose -f docker/docker-compose.yml ps

prod_ps:
	@docker-compose -f docker/docker-compose-production.yml --project-name="prod_" ps
