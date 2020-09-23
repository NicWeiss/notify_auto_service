<?php

namespace generic;

use lib\request;


class component{

    protected static $code = 200;
    protected static $answer = [];

    public static function get_answer(){
        return self::$answer;
    }

    protected static function set_data($data){
        self::$answer['code'] = 200;
        self::$answer['data'] = $data;
    }

    protected static function has_no_permission(){
        self::$answer['code'] = 403;
        self::$answer['data'] = 'You do not have access';
    }

    protected static function not_found(){
        self::$answer['code'] = 404;
        self::$answer['data'] = 'Not found';
    }

    protected static function critical_error(){
        self::$answer['code'] = 500;
        self::$answer['data'] = 'Critical error';
    }

    protected static function unprocessable_entity(){
        self::$answer['code'] = 422;
        self::$answer['data'] = 'Unprocessable entity';
    }
}