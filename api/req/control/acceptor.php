<?php

/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\BaseController;
use lib\request;
use model\acceptor_model;

class acceptor extends BaseController
{
    public static function post()
    {
        $acceptor = acceptor_model::create_acceptor(self::$request_json, self::$user);
        self::set_data($acceptor);
    }

    public static function get()
    {
        $page = request::get('page') ? request::get('page') : 0;
        $per_page = request::get('per_page') ? request::get('per_page') : 25;

        $acceptor = acceptor_model::get_acceptors(self::$user, $page, $per_page);
        self::set_data($acceptor);
        self::set_total_pages(round(acceptor_model::get_total(self::$user) / $per_page) + 1);
    }

    public static function get_by_id($entity_id)
    {
        $notify = acceptor_model::get_acceptor($entity_id, self::$user);
        self::set_data($notify);
    }

    public static function update($entity_id)
    {
        $notify = acceptor_model::update_acceptor($entity_id, self::$request_json, self::$user);
        self::set_data($notify);
    }

    public static function delete($entity_id)
    {
        $notify = acceptor_model::delete_acceptor($entity_id, self::$user);
        self::set_data($notify);
    }
}
