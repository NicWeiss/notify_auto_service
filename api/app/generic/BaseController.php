<?php

namespace generic;

use Exception;
use model\auth_base as auth;
use lib\request;

class BaseController
{

    protected static $model_name = null;
    protected static $answer = [];
    protected static $user = null;
    protected static $total = 0;
    protected static $request_json;


    function __construct()
    {
        self::$request_json = self::getModelData();
    }

    public static function set_total_pages($total)
    {
        self::$total = $total;
    }

    public static function get_total_pages()
    {
        return self::$total;
    }

    public static function set_session($user_session_id)
    {
        self::$user = auth::get_user_by_session($user_session_id);
    }

    protected static function has_no_permission()
    {
        return new Exception(self::error('You do not have access'), 403);
    }

    protected static function not_found()
    {
        return new Exception(self::error('Not found'), 404);
    }

    protected static function critical_error()
    {
        return new Exception(self::error('Critical error'), 404);
    }

    protected static function unprocessable_entity()
    {
        return new Exception(self::error('Unprocessable entity'), 422);
    }

    private static function getModelData()
    {
        if (!json_decode(file_get_contents('php://input'))) {
            return;
        }

        self::$model_name = key(json_decode(file_get_contents('php://input'), true));
        if (!self::$model_name) {
            return;
        }
        return request::get_from_client_Json(self::$model_name);
    }

    public static function post()
    {
        throw self::not_found();
    }

    public static function get()
    {
        throw self::not_found();
    }

    public static function get_by_id($entity_id)
    {
        throw self::not_found();
    }

    public static function update($entity_id)
    {
        throw self::not_found();
    }

    public static function delete($entity_id)
    {
        throw self::not_found();
    }

    private static function error($message)
    {
        return json_encode(['errors' => ['error' => $message]]);
    }
}
