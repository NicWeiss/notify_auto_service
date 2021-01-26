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
    public static function post()
    {
        $data = self::getModelData();
        $notify = nf::create_notify($data, self::$user);
        self::set_data($notify);
    }

    public static function get()
    {
        $page = request::get('page');
        $per_page = request::get('per_page');
        $notify = nf::get_all_notify(self::$user, $page, $per_page);
        self::set_total_pages(round(nf::get_total(self::$user) / $per_page) + 1);
        self::set_data($notify);
    }

    public static function get_by_id($entity_id)
    {
        $notify = nf::get_notify($entity_id, self::$user);
        self::set_data($notify);
    }

    public static function update($entity_id)
    {
        $data = self::getModelData();
        $notify = nf::update_notify($entity_id, $data, self::$user);
        self::set_data($notify);
    }

    public static function delete($entity_id)
    {
        $notify = nf::delete_notify($entity_id, self::$user);
        self::set_data($notify);
    }
}
