<?php
header("Access-Control-Allow-Origin: *");
use lib\request as request;


require_once("req/std.php");
require_once('tmp/config.ini.php');
std_env_init();


final class myapp
{
    public static function run(){
        request :: init();
        $object = dispatcher :: dispatch();
        std_autoload($object['control_class']);
        $class = $object['control_class'];
        $method = $object["control_function"];
        echo json_encode($class::$method());
    }

}
myapp :: run();
