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
        $acceptor = am::create_acceptor($data, self::$user);
        self::set_data($acceptor);
    }

    public static function get()
    {
        $acceptor = am::get_acceptors(self::$user);
        self::set_data($acceptor);
    }

    public static function update($entity_id)
    {
        $data = self::getModelData();
//        $acceptor = am::create_acceptor($data, self::$user);
//        std_debug($acceptor);
        self::set_data(['id' => $entity_id, 'status' => 'UPDATED']);
    }
}
