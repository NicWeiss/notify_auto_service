<?php

namespace control;

use generic\BaseController;
use model\CategoryModel as model;

class Category extends BaseController
{
    public static function post()
    {
        $name = self::$request_model['name'];
        $category = model::create(self::$user['id'], $name);
        return $category;
    }

    public static function get()
    {
        $category = model::get(self::$user['id']);
        return $category;
    }

    public static function get_by_id($entity_id)
    {
        $category = model::get(self::$user['id'], $entity_id);
        return $category;
    }

    public static function update($entity_id)
    {
        $name = self::$request_model['name'];
        $is_hidden =  self::$request_model['is_hidden'];

        $category = model::update(self::$user['id'], $entity_id, $name, $is_hidden);
        return $category;
    }

    public static function delete($entity_id)
    {
        $category = model::delete(self::$user['id'], $entity_id,);
        return $category;
    }
}
