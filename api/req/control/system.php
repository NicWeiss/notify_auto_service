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
    public static function get_all()
    {
        $systems = sm::get_all();
        self::set_data($systems);
    }
}
