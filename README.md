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

    - make start


more commands - make help

запуск контейнеров для бэкэеда
```bash
cd docker
docker-compose up postgres redis frontend postfix rabbit flower
```

генерация ключей для SMTP
```bash
opendkim-genkey --bits 1024 --domain nic-weiss.tech  -s mail -v
```

данные для DKIM будут внутри mail.txt

запуск проекта локально в VENV
```bash
cd backend
bash -c "export $(cat .env | xargs) && uvicorn main:app --reload --host 0.0.0.0 --port 8800"
```

Создание нового файла миграции локально в VENV
```bash
bash -c "export $(cat .env | xargs) && alembic revision --autogenerate -m 'Field changes'"
```

Обновление голов локально в VENV
```bash
bash -c "export $(cat .env | xargs) && alembic upgrade heads"
```

Запуск celery
```bash
bash -c "export $(cat .env | xargs) && celery -A app.celery_tasks worker --loglevel=info --concurrency=1 -E -n notifier-worker@4.0.0.%h -Q watcher,sender"
```
