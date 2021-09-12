FROM php:7.4-cli

RUN apt-get update; apt-get install -y libcurl4-openssl-dev libonig-dev libzip-dev zip

RUN docker-php-ext-install curl
RUN docker-php-ext-enable curl

RUN docker-php-ext-install mbstring
RUN docker-php-ext-enable mbstring

RUN docker-php-ext-install zip
RUN docker-php-ext-enable zip

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

RUN mkdir -p /usr/lib/php/modules/

RUN cp /usr/local/lib/php/extensions/no-debug-non-zts-20190902/* /usr/lib/php/modules/

CMD ["php", "-a"]
