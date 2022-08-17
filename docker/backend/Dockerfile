FROM php:7.4-fpm  as base

RUN apt-get update; apt-get install -y libcurl4-openssl-dev libonig-dev libzip-dev zip nginx cron unzip

RUN curl -O -L "https://github.com/grafana/agent/releases/download/v0.26.1/agent-linux-amd64.zip"
RUN unzip "agent-linux-amd64.zip"
RUN chmod a+x "agent-linux-amd64"
RUN mv agent-linux-amd64 /usr/bin/grafana-agent
RUN rm agent-linux-amd64.zip

RUN rm -rf /etc/nginx/sites-enabled
COPY ./docker/backend/nginx.conf /etc/nginx/conf.d/default.conf

RUN docker-php-ext-install curl
RUN docker-php-ext-enable curl

RUN docker-php-ext-install mbstring
RUN docker-php-ext-enable mbstring

RUN docker-php-ext-install zip
RUN docker-php-ext-enable zip

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql
RUN mkdir -p /usr/lib/php/modules/
RUN cp /usr/local/lib/php/extensions/no-debug-non-zts-20190902/* /usr/lib/php/modules/

COPY ./api /var/www
WORKDIR /var/www

COPY ./docker/backend/cronfile /etc/cron.d/cronfile
RUN chmod 0644 /etc/cron.d/cronfile
RUN crontab /etc/cron.d/cronfile
RUN touch /var/log/cron.log
RUN touch /container.env
RUN touch /tmp/log.txt

RUN touch /start.sh
RUN echo '#!/bin/bash' >> /start.sh
RUN echo 'declare -p | grep -Ev "BASHOPTS|BASH_VERSINFO|EUID|PPID|SHELLOPTS|UID" > /container.env' >> /start.sh
RUN echo 'grafana-agent -config.file /etc/agent/agent.yaml -server.http.address="0.0.0.0:12345" > /dev/null 2>&1 &' >> /start.sh
RUN echo 'cron &' >> /start.sh
RUN echo 'nginx &' >> /start.sh
RUN echo 'php-fpm &' >> /start.sh
RUN echo 'echo --------------- BACKEND STARTED ---------------'  >> /start.sh
RUN echo 'tail -f /tmp/log.txt' >> /start.sh
RUN chmod +x /start.sh

CMD ["sh", "/start.sh"]

# ---- Test build ----
FROM base as test
RUN touch /start_test.sh
RUN echo '#!/bin/bash' >> /start_test.sh
RUN echo 'cron &' >> /start_test.sh
RUN echo 'nginx &' >> /start_test.sh
RUN echo 'php-fpm &' >> /start_test.sh
RUN echo 'tail -f /tmp/log.txt' >> /start_test.sh
RUN chmod +x /start_test.sh

CMD ["sh", "/start_test.sh"]
