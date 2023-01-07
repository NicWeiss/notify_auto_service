<?php

namespace generic;

use generic\BaseClass;
use lib\Request;

class BaseController extends BaseClass
{

    protected static $model_name = null;
    protected static $answer = [];
    protected static $total = 0;
    protected static $request_model;
    protected static $request_json;
    protected static $query_params;


    function __construct()
    {
        self::$request_json = self::getDataFromRequestJson();
        self::$request_model = self::getModelDataFromRequest();
        self::$query_params = self::getQueryParams();
    }

    public static function set_total_pages($total)
    {
        self::$total = $total;
    }

    public static function get_total_pages()
    {
        return self::$total;
    }

    private static function getDataFromRequestJson()
    {
        if (!json_decode(file_get_contents('php://input'))) {
            return [];
        }
        return json_decode(file_get_contents('php://input'), true);
    }

    private static function getModelDataFromRequest()
    {
        if (count(array_keys(self::$request_json)) != 1) {
            return [];
        }

        self::$model_name = key(self::$request_json);
        if (!self::$model_name) {
            return;
        }

        return Request::get_from_client_Json(self::$model_name);
    }

    private static function getQueryParams()
    {
        return Request::$query_params;
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
}
