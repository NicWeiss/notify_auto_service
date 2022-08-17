<?php

require_once("app/std.php");
require_once("testing/lib/autoload.php");

use lib\DB;
use lib\Request;
use PHPUnit\TextUI\Command;
use lib\migration\Controller as MigrationController;


function prepare_database()
{
    echo "Prepare DB \n";
    $db_name = $GLOBALS['config']::$db_name;

    $query = "DROP DATABASE $db_name;";
    DB::query($query);
    $query = "CREATE DATABASE $db_name;";
    DB::query($query);
    MigrationController::init();
    MigrationController::up(true);
}


function run_testing()
{
    Request::init();

    try {
        $command = new Command();
        $command->run(['phpunit', 'testing/packages']);
    } catch (Exception $e) {
        var_dump($e);
    }
}


std_env_init();
autoload_phpunit();
prepare_database();
run_testing();
