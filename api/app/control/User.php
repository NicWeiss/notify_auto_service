<?php

namespace control;

use generic\BaseController;
use model\UserModel;
use model\AcceptorModel;
use model\NotifyModel;
use model\CategoryModel;
use model\SystemModel;
use model\SessionModel;

class User extends BaseController
{
    public static function post()
    {
    }

    public static function get()
    {
        $user = UserModel::get(self::$user['id']);
        return $user;
    }

    public static function update($entity_id)
    {
        return UserModel::update($entity_id, self::$request_model);
    }

    public static function delete($entity_id = null)
    {
        $current_password = md5(self::$request_json['password']);

        if (self::$user['password'] != $current_password) {
            throw self::unprocessable_entity();
        }

        NotifyModel::delete_all(self::$user['id']);
        AcceptorModel::delete_all(self::$user['id']);
        CategoryModel::delete_all(self::$user['id']);
        NotifyModel::delete_all(self::$user['id']);
        SystemModel::delete_all(self::$user['id']);
        SessionModel::delete_all(self::$user['id']);
        UserModel::delete(self::$user['id']);

        // $category = model::delete(self::$user['id'], $entity_id,);
        // return $category;
        return true;
    }
}
