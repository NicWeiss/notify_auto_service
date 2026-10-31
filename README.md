# Сервис персональных оповещений. #

  ## Установка: ##
    - install Docker and docker-compose in your system
    - add your user in docker group
    - enable and start systemd docker socket

    Open this poject / docker in terminal

    - make build
    - make migration init
    - make migration up

  ## Запуск: ##

    - cp backend_go/.env.example backend_go/.env  (и заполнить)
    - make start


more commands - make help

Бэкэнд написан на Go (`backend_go`). Один бинарник отдаёт API (`/api/v3`, для старых клиентов
также `/api/v2`) и сам выполняет периодические задачи (отслеживание времени, рассылка,
очистка БД). Миграции БД (`backend_go/src/core/migrations/*.sql`) применяются при старте.
Рассылка: `time watcher` создаёт операцию на каждую минуту, `matcher` одним запросом находит
уведомления и кладёт сообщения в очередь `delivery`, диспетчеры (по пулу воркеров на канал)
отправляют их параллельно, повторяют при сбоях (10с, 1м, 5м, 30м) и не теряют при перезапуске.
Параметры - в `backend_go/.env.example`. После простоя сервиса проверяются все пропущенные минуты
(`CATCH_UP_MINUTES=0`). Уже потерянный период можно отправить командой
`notifier backfill -from "2026-05-07 07:00" -to "2026-10-30 19:00"` (UTC; без `-apply` только считает).
Документация API: `/docs/index.html`.

запуск контейнеров для бэкэнда
```bash
cd docker
docker-compose up postgres frontend postfix
```

генерация ключей для SMTP
```bash
opendkim-genkey --bits 1024 --domain nic-weiss.tech  -s mail -v
```

данные для DKIM будут внутри mail.txt

запуск бэкэнда локально (с автоперезапуском)
```bash
cd backend_go
bash run.sh
```

тесты
```bash
make test
```

Новая миграция БД: `make migration_create msg=add_field`, затем написать SQL в созданном файле
(SQL должен быть идемпотентным, файлы применяются по порядку имён один раз).
