<?php

/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\BaseController;
use lib\request;
use model\AcceptorModel;

class acceptor extends BaseController
{
    public static function post()
    {
        $acceptor = AcceptorModel::create_acceptor(self::$request_model, self::$user);
        return $acceptor;
    }

    public static function get()
    {
        $page = request::get('page') ? request::get('page') : 0;
        $per_page = request::get('per_page') ? request::get('per_page') : 25;

        $acceptor = AcceptorModel::get_acceptors(self::$user, $page, $per_page);
        self::set_total_pages(round(AcceptorModel::get_total(self::$user) / $per_page) + 1);
        return $acceptor;
    }

    public static function get_by_id($entity_id)
    {
        $notify = AcceptorModel::get_acceptor($entity_id, self::$user);
        return $notify;
    }

    public static function update($entity_id)
    {
        $notify = AcceptorModel::update_acceptor($entity_id, self::$request_model, self::$user);
        return $notify;
    }

    public static function delete($entity_id)
    {
        $notify = AcceptorModel::delete_acceptor($entity_id, self::$user);
        return $notify;
    }
}
