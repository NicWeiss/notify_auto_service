<?php

namespace generic;


use model\auth_base as auth;
use lib\request;

class component
{

    protected static $model_name = null;
    protected static $answer = [];
    protected static $http_code = 200;
    protected static $user = null;
    protected static $total = 0;

    public static function get_answer($ember_model)
    {
        if ($ember_model) {
            return [
                $ember_model => self::$answer,
                'meta' => [
                    'total_pages' => self::$total
                ]
            ];
        }
        return self::$answer;
    }

    public static function set_total_pages($total)
    {
        self::$total = $total;
    }

    public static function set_session($user_session_id)
    {
        self::$user = auth::get_user_by_session($user_session_id);
    }

    public static function get_http_responce_code()
    {
        return self::$http_code;
    }

    protected static function set_data($data)
    {
        self::$http_code = 200;
        self::$answer = $data;
    }

    protected static function has_no_permission()
    {
        self::$http_code = 403;
        self::$answer = 'You do not have access';
    }

    protected static function not_found()
    {
        self::$http_code = 404;
        self::$answer = 'Not found';
    }

    protected static function critical_error()
    {
        self::$http_code = 500;
        self::$answer = 'Critical error';
    }

    protected static function unprocessable_entity()
    {
        self::$http_code = 422;
        self::$answer = 'Unprocessable entity';
    }

    public static function getModelData()
    {
        self::$model_name = key(json_decode(file_get_contents('php://input'), true));
        return request::get_from_client_Json(self::$model_name);
    }

    public static function post()
    {
        self::not_found();
    }

    public static function get()
    {
        self::not_found();
    }

    public static function get_by_id($entity_id)
    {
        self::not_found();
    }

    public static function update($entity_id)
    {
        self::not_found();
    }

    public static function delete($entity_id)
    {
        self::not_found();
    }
}
