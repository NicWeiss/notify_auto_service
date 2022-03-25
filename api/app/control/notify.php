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
        $page = request::get('page') ? request::get('page') : 1;
        $per_page = request::get('per_page') ? request::get('per_page') : 25;
        $category_id = request::get('category_id') ? request::get('category_id') : 0;

        $notify = notify_model::get_all_notify(self::$user, $category_id, $page, $per_page);
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
        if ($notify) {
            return $notify;
        } else {
            throw self::critical_error();
        }
    }

    public static function delete($entity_id)
    {
        $notify = notify_model::delete_notify($entity_id, self::$user);
        return $notify;
    }

    public static function delete_by_category_id()
    {
        $category_id = request::get_from_client_Json('category_id');
        $notify = notify_model::delete_by_category_id($category_id, self::$user);
        return $notify;
    }

    public static function reset_from_category_id()
    {
        $category_id = request::get_from_client_Json('category_id');
        $notify = notify_model::move_notifies($category_id, 0, self::$user);
        return $notify;
    }
}
