<?php

namespace generic;

use Exception;
use model\auth_base as auth;
use lib\request;

class BaseModel
{
    function __construct()
    {
    }

    private function get_model_properties()
    {
        return array_keys(get_class_vars(get_class($this)));
    }

    public static function select($filter = null)
    {
    }
}
