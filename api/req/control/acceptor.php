<?php
/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\component;
use lib\email;
use lib\request;
use model\acceptor_model as am;

class acceptor extends component
{
    public static function add()
    {
        $data = self::getModelData();
//        $acceptor = am::careate_acceptor($data);
        std_debug($data);
        self::set_data(['id' => 0, 'status' => 'enabled']);
    }
}
