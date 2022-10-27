<?php

require_once("app/std.php");
std_env_init();

use lib\Config;
use lib\migration\Controller as MigrationController;
$GLOBALS['config'] = new Config();

function print_help()
{
    die("usage:
    php migration.php  {command} [params]
commands:
    init                            - Инициализация таблицы миграций
    create                          - Создать миграцию;
    up [count]                      - Применить миграции (применит все доступные миграции);
    down [count|migration_id]       - Откатить миграции (default count=1);
    stat                            - Показать доступные миграции без применения.
    resolve                         - Попытаться найти и откатить миграцию которая была удалена с диска, но есть базе.
");
}

if ($argc < 2)
    print_help();

for ($command_inex = 1; $command_inex < $argc; $command_inex++) {
    $command = $argv[$command_inex];
    switch ($command) {
        case 'create':
            MigrationController::create();
            break;

        case 'stat':
            MigrationController::status();
            break;

        case 'up':
            MigrationController::up();
            break;

        case 'down':
            $count = 0;

            if (isset($argv[$command_inex + 1])) {
                $count = isset($argv[$command_inex + 1]);
            }

            MigrationController::down($count);
            break;

        case 'resolve':
            MigrationController::resolve();
            break;

        case 'init':
            MigrationController::init();
            break;

        default:
            print_help();
            break;
    }
}
