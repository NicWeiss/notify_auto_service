<?php

/**
 * class stub
 * return if try call wrong route
 */

namespace control;

use generic\BaseController;

class stub extends BaseController
{
    public static function init()
    {
        $array = [
            "list" => [
                "id" => 0,
                "message" => "некорректный адресс"
            ]
        ];
        return $array;
    }
}
