# Сервис персональных оповещений. #

  ## Установка: ##

    - Установка nginx, php-fpm, mysql, npm, ember
    - Конфигурирование nginx:
      - Использовать nginx.conf
    - Конфигурирование php-fpm
      - В php.ini раскоментировать mysql.so драйвер
    - Конфигурирование mysql:
      - Завести пользователя, задать пароль
    -Демоны:
      - Делаем `sudo systemctl enable ...` для nginx, php-fpm, mysql
      - Делаем `sudo systemctl start ...` для nginx, php-fpm, mysql
    - Добавляем хост в `/etc/hosts`
    - Устанавливаем NPM а с помощью него ember js и зависимости проекта
      - `sudo npm install -g ember-cli`
      - `Snpm install`

  ## Запуск: ##

    - Создание конфига для проекта:
      - Конфиг должен содержать все заполненные поля по аналогии с примером
      - При первом запуске будет создана база с именем указанным в конфиге, если её ещё нет
      - Для актуализации состояния БД мигрируем её из директории api
        - `php migration.php init`
        - `php migration.php stat`
        - `php migration.php up`

    - Для запуска frontend части в режиме livereload используем команду `ember s` из директории frontend