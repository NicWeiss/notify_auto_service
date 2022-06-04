<?php

// Waiting database start ...
sleep(2);

require_once("app/std.php");
require_once("testing/phpunit");

use lib\dba;
use lib\request as request;
use PHPUnit\TextUI\Command;
use lib\migration\Controller as MigrationController;


function prepare_database()
{
    echo "Prepare DB \n";
    $db_name = $GLOBALS['config']::$db_name;

    $query = "DROP DATABASE $db_name;";
    dba::query($query);
    $query = "CREATE DATABASE $db_name;";
    dba::query($query);
    MigrationController::init();
    MigrationController::up(true);
}


function run_testing()
{
    request::init();

    $command = new Command();
    $command->run(['phpunit', 'testing/packages']);
}


std_env_init();
prepare_database();
run_testing();
