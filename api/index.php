<?php
header("Access-Control-Allow-Origin: *");
use lib\request as request;


require_once("req/std.php");
require_once('tmp/config.ini.php');
http_response_code(200);
std_env_init();


final class myapp
{
    public static function run(){
        request :: init();

        $object = dispatcher :: dispatch();
        std_autoload($object['control_class']);

        $class = $object['control_class'];
        $method = $object["control_function"];
        $ember_model = is_null($object["ember_model"]) ? False : $object["ember_model"];

        $class::$method();
        $answer = $class::get_answer($ember_model);

        if (!$answer) {
            http_response_code(404);
        } else {
            http_response_code($class::get_http_responce_code());
            echo json_encode($answer);
        }
    }

}
myapp :: run();
