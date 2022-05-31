<?php

/**
 * class system
 * отвечает за управление системами получения
 */

namespace control;

use generic\BaseController;
use model\SystemModel;

class system extends BaseController
{
    public static function get()
    {
        $systems = SystemModel::get_all();
        return $systems;
    }

    public static function get_by_id($entity_id)
    {
        $system = SystemModel::get_system($entity_id);
        return $system;
    }
}
