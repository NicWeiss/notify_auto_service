<?php
/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\component;
use lib\email;
use lib\request;
use model\notify_model as nf;

class notify extends component
{
    public static function add()
    {
        $data = self::getModelData();
        $notify = nf::create_notify($data, self::$user);
        self::set_data($notify);
    }

    public static function get()
    {
        $acceptor = nf::get_all_notify(self::$user);
        self::set_data($acceptor);
    }

    public static function update($entity_id)
    {
        $data = self::getModelData();
//        $acceptor = am::create_acceptor($data, self::$user);
//        std_debug($acceptor);
        self::set_data(['id' => $entity_id, 'status' => 'TEST']);
    }
}
