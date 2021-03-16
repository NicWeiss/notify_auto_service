SHELL=/bin/sh

DIR=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))

# Colors
Color_Off=\033[0m
Cyan=\033[1;36m
Red=\033[1;31m


help:  ## Помощь
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

### Development
build:  ## Сборка проекта
	@docker-compose build backend frontend cron mysql php-cli php-fpm

build_production:  ## Сборка проекта
	@docker-compose build backend frontend-production cron mysql php-cli php-fpm

start_develop: build  ## Запуск проекта для разработки
	@docker-compose up backend frontend cron

start_production: build_production  ## Запуск проекта
	@docker-compose up backend frontend-production cron

stop: ## Остановка проекта
	@docker-compose down

migration:  ## Создание новой миграции
	docker-compose run --user www-data php-cli sh -c "cd /var/www && php migration.php $(filter-out $@,$(MAKECMDGOALS))"
	@sudo chown $$USER:$$USER -R $(DIR)/../api/req/migration/

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

dump_restore:  ## Восстановление базы из бэкапа
	docker-compose run mysql bash -c "cd mysql && mysql -h mysql -u root -pmysql notifier < dump.sql"

dump_create:  ## Создание бекапа базы
	docker-compose run mysql bash -c "cd mysql && mysqldump -h mysql -u root -pmysql notifier > dump.sql"


