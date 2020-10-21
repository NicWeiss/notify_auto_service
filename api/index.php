<?php
header("Access-Control-Allow-Origin: *");

use lib\request as request;


require_once("req/std.php");
require_once('tmp/config.ini.php');
http_response_code(200);
std_env_init();


final class myapp
{
    public static function run()
    {
        request:: init();
        $user_session_id = null;

        foreach (getallheaders() as $key => $value) {
            if ($key == 'Session') {
                $user_session_id = $value;
                //TO-DO добавить проверку валидности полученного токена сессии
            }
        }

        $object = dispatcher:: dispatch();

        $class = $object['control_class'];

        $method = array_key_exists("control_function", $object) ?  $object["control_function"] : 'get';
        $entity_id = array_key_exists("entity_id", $object) ? $object["entity_id"] : False;
        $ember_model = array_key_exists("ember_model", $object) ? $object["ember_model"] : False;

        if (!$user_session_id && $class != 'control\auth') {
            http_response_code(403);
            return;
        }

        $class::set_session($user_session_id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($entity_id) {
                $class::$method($entity_id);
            } else {
                $class::$method();
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($entity_id) {
                $class::get_by_id($entity_id);
            } else {
                $class::get();
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $class::update($entity_id);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $class::delete($entity_id);
        }

        $answer = $class::get_answer($ember_model);

        if (!$answer) {
            http_response_code(404);
        } else {
            http_response_code($class::get_http_responce_code());
            echo json_encode($answer);
        }
    }

}

myapp:: run();
