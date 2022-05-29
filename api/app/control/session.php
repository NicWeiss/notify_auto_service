<?php

/**
 * class session
 * отвечает за управление сессиями
 */

namespace control;

use generic\BaseController;
use model\SessionModel;

class session extends BaseController
{
    public static function get()
    {
        return SessionModel::get_all(self::$user['id']);
    }

    public static function get_by_id($entity_id)
    {
        return SessionModel::get_session(self::$user['id'], $entity_id);
    }

    public static function delete($entity_id)
    {
        return SessionModel::delete_session(self::$user['id'], $entity_id);
    }
}
