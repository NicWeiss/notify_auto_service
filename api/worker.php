<?php
header("Access-Control-Allow-Origin: *");


require_once("req/std.php");
require_once('tmp/config.ini.php');
std_env_init();


final class worker
{
    public static function run()
    {
        \services\worker::run();
    }
}

worker::run();
