<?php
/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\component;
use lib\email;
use lib\request;
use model\auth_base as auth_base;

class acceptor extends component
{
    public static function add()
    {
        $data = self::getModelData();
        
        self::set_data(['id' => 0, 'status' => 'enabled']);
    }
}
