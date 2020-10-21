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
        $notify = am::update_acceptor($entity_id, $data, self::$user);
        self::set_data($notify);
    }

    public static function delete($entity_id)
    {
        $notify = am::delete_acceptor($entity_id, self::$user);
        self::set_data($notify);
    }
}
