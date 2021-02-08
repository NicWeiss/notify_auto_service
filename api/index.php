<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

use lib\request as request;
use lib\rest as rest;
use model\auth_base;

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
                $is_session_valid = auth_base::is_session_valid($value);
                if (!$is_session_valid) {
                    http_response_code(403);
                    return;
                }
            }
        }

        $object = dispatcher::dispatch();

        $class = new $object['control_class'];

        $method = array_key_exists("control_function", $object) ?  $object["control_function"] : 'post';
        $entity_id = array_key_exists("entity_id", $object) ? $object["entity_id"] : False;
        $ember_model = array_key_exists("ember_model", $object) ? $object["ember_model"] : False;

        if (!$user_session_id && $object['control_class'] != 'control\auth') {
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
