<?php

namespace generic;


use lib\request;

class component
{

    protected static $is_model = false;
    protected static $model_name = null;
    protected static $answer = [];
    protected static $http_code = 200;

    public static function get_answer()
    {
        if (self::$is_model){
            return [ self::$model_name => self::$answer];
        }
        return self::$answer;
    }

    public static function get_http_responce_code() {
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
        self::$is_model = true;
        self::$model_name = key(json_decode(file_get_contents('php://input'), true));
        return request::get_from_client_Json(self::$model_name);
    }
}