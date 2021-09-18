<?php

/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\BaseController;
use lib\request;
use model\notify_model;

class notify extends BaseController
{
    public static function post()
    {
        $notify = notify_model::create_notify(self::$request_json, self::$user);
        return $notify;
    }

    public static function get()
    {
        $page = request::get('page');
        $per_page = request::get('per_page');
        $notify = notify_model::get_all_notify(self::$user, $page, $per_page);
        self::set_total_pages(round(notify_model::get_total(self::$user) / $per_page) + 1);
        return $notify;
    }

    public static function get_by_id($entity_id)
    {
        $notify = notify_model::get_notify($entity_id, self::$user);
        return $notify;
    }

    public static function update($entity_id)
    {
        $notify = notify_model::update_notify($entity_id, self::$request_json, self::$user);
        return $notify;
    }

    public static function delete($entity_id)
    {
        $notify = notify_model::delete_notify($entity_id, self::$user);
        return $notify;
    }
}
