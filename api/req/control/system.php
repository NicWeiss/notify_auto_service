<?php
/**
 * class system
 * отвечает за управление системами получения
 */

namespace control;

use generic\component;
use model\system_model as sm;

class system extends component
{
    public static function get()
    {
        $systems = sm::get_all();
        self::set_data($systems);
    }

    public static function get_by_id($entity_id)
    {
        $system = sm::get_system($entity_id);
        self::set_data($system);
    }
}
