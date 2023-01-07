<?php

namespace generic;

use Exception;
use model\AuthModel as auth;

class BaseClass
{

    protected static $user = null;


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
        return new Exception(self::error('Critical error'), 500);
    }

    protected static function unprocessable_entity()
    {
        return new Exception(self::error('Unprocessable entity'), 422);
    }

    private static function error($message)
    {
        return json_encode(['errors' => ['error' => $message]]);
    }
}
