<?php
header("Access-Control-Allow-Origin: *");

use lib\request as request;


require_once("req/std.php");
require_once('tmp/config.ini.php');
std_env_init();


final class watcher
{
    public static function run()
    {
        \services\watcher::run();
    }
}

watcher::run();
