<?php

/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\BaseController;
use lib\Request;
use model\AcceptorModel;
use services\FcmService;

class Acceptor extends BaseController
{
    public static function post()
    {
        $acceptor = AcceptorModel::create_acceptor(self::$request_model, self::$user);
        return $acceptor;
    }

    public static function get()
    {
        $page = Request::get('page') ? Request::get('page') : 0;
        $per_page = Request::get('per_page') ? Request::get('per_page') : 25;

        $acceptor = AcceptorModel::get_acceptors(self::$user, $page, $per_page);
        self::set_total_pages(round(AcceptorModel::get_total(self::$user) / $per_page) + 1);
        return $acceptor;
    }

    public static function get_by_id($entity_id)
    {
        $acceptor = AcceptorModel::get_acceptor($entity_id, self::$user);
        return $acceptor;
    }

    public static function update($entity_id)
    {
        $acceptor = AcceptorModel::get_acceptor($entity_id, self::$user);
        if ($acceptor && $acceptor['is_system'] == true) {
            $acceptor['status'] = self::$request_model['status'];
            $data = $acceptor;
        } else {
            $data = self::$request_model;
        }

        $acceptor = AcceptorModel::update_acceptor($entity_id, $data, self::$user);

        return $acceptor;
    }

    public static function delete($entity_id)
    {
        $acceptor = AcceptorModel::get_acceptor($entity_id, self::$user);
        if ($acceptor && $acceptor['is_system'] == true) {
            throw self::has_no_permission();
        }

        $acceptor = AcceptorModel::delete_acceptor($entity_id, self::$user);
        return $acceptor;
    }

    public static function update_push_acceptor()
    {
        $new_fcm_token = self::$request_json['fcm_token'];
        $fcm_service = new FcmService(self::$user['id']);

        $fcm_service->add_fcm_token($new_fcm_token);

        return 'ok';
    }
}
