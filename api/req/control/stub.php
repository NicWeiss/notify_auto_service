<?php
/**
 * class stub
 * return if try call wrong route
 */
namespace control;

use generic\component;

class stub extends component
{
    public static function init(){
        $array = [
            "list" => [
                "id" => 0,
                "message" => "некорректный адресс"]
        ];
        return $array;
    }
}