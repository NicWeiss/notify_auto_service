<?php

namespace control;

use generic\BaseController;
use model\UserModel as model;

class user extends BaseController
{
    public static function post()
    {
        // $name = self::$request_json['name'];
        // $category = model::create(self::$user['id'], $name);
        // return $category;
    }

    public static function get()
    {
        $user = model::get(self::$user['id']);
        return $user;
    }

    public static function update($entity_id)
    {
        // $name = self::$request_json['name'];
        // $category = model::update(self::$user['id'], $entity_id, $name);
        // return $category;
    }

    public static function delete($entity_id)
    {
        // $category = model::delete(self::$user['id'], $entity_id,);
        // return $category;
    }
}