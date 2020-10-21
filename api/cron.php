<?php
header("Access-Control-Allow-Origin: *");

use lib\request as request;


require_once("req/std.php");
require_once('tmp/config.ini.php');
std_env_init();


final class myapp
{
    public static function run()
    {
        \control\send::run();
    }

}

myapp:: run();
