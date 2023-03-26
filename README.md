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


генерация ключей для SMTP
opendkim-genkey --bits 1024 --domain nic-weiss.tech  -s mail -v

данные для DKIM будут внутри mail.txt

запуск проекта локально в VENV
cd backend
bash -c "export $(cat .env | xargs) && uvicorn main:app --reload --host 0.0.0.0 --port 8800"
