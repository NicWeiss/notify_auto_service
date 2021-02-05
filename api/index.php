<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400000');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
}

use lib\request as request;
use lib\rest as rest;


require_once("req/std.php");
require_once('tmp/config.ini.php');
http_response_code(200);
std_env_init();


final class myapp
{
    public static function run()
    {
        request::init();
        $user_session_id = null;

        foreach (getallheaders() as $key => $value) {
            if ($key == 'Session') {
                $user_session_id = $value;
                //TO-DO добавить проверку валидности полученного токена сессии
            }
        }

        $object = dispatcher::dispatch();

        $class = new $object['control_class'];

        $method = array_key_exists("control_function", $object) ?  $object["control_function"] : 'post';
        $entity_id = array_key_exists("entity_id", $object) ? $object["entity_id"] : False;
        $ember_model = array_key_exists("ember_model", $object) ? $object["ember_model"] : False;

        if (!$user_session_id && $class != 'control\auth') {
            http_response_code(403);
            return;
        }

        $class::set_session($user_session_id);

        rest::process($class, $method, $entity_id);

        $answer = $class::get_answer($ember_model);

        if (!$answer) {
            http_response_code(404);
        } else {
            http_response_code($class::get_http_responce_code());
            echo json_encode($answer);
        }
    }
}

myapp::run();
