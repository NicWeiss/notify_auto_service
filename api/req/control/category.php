<?php

namespace control;

use generic\BaseController;
use model\CategoryModel as model;

class category extends BaseController
{
    public static function post()
    {
        $name = self::$request_json['name'];
        $category = model::create(self::$user, $name);
        return $category;
    }

    public static function get()
    {
        $category = model::get(self::$user);
        return $category;
    }

    public static function get_by_id($entity_id)
    {
        $category = model::get(self::$user, $entity_id);
        return $category;
    }

    public static function update($entity_id)
    {
        $name = self::$request_json['name'];
        $category = model::update(self::$user, $entity_id, $name);
        return $category;
    }

    public static function delete($entity_id)
    {
        $category = model::delete(self::$user, $entity_id,);
        return $category;
    }
}
