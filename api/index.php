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

use lib\Config;
use lib\Request;
use lib\RestAdapter;
use model\AuthModel;

require_once("app/std.php");
http_response_code(500);
std_env_init();


final class myapp
{
    public static function run()
    {
        $GLOBALS['config'] = new Config();
        $user_session_id = null;

        foreach (getallheaders() as $key => $value) {
            if ($key == 'Session') {
                $user_session_id = $value;
                $is_session_valid = AuthModel::is_session_valid($value);
            }
        }

        $object = Dispatcher::dispatch();

        $class = new $object['control_class'];

        $method = array_key_exists("control_function", $object) ?  $object["control_function"] : 'post';
        $entity_id = array_key_exists("entity_id", $object) ? $object["entity_id"] : False;
        $ember_model = array_key_exists("ember_model", $object) ? $object["ember_model"] : False;

        if ($object['control_class'] != 'control\Auth') {
            if (!$user_session_id || !$is_session_valid) {
                http_response_code(403);
                return;
            }
        }

        $class::set_session($user_session_id);

        $result = null;

        try {
            $result = RestAdapter::excute($class, $method, $ember_model, $entity_id);
        } catch (Exception $exc) {
            http_response_code($exc->getCode());
            echo $exc->getMessage();
            return;
        }

        if (!$result) {
            http_response_code(404);
        } else {
            echo json_encode($result);
        }
    }
}


Request::init();
myapp::run();
