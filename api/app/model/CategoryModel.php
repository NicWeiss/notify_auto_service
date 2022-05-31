<?php

namespace model;

use helpers\Logger;
use lib\dba as dba;

final class CategoryModel
{
    public static $table = TABLE_OF_CATEGORIES;


    public static function get($user_id, $id = null)
    {
        $where_by_id = $id ? " and `id`=$id" : "";
        $sql = "SELECT * FROM  " . self::$table  . " where `user_id` = $user_id" .  $where_by_id;
        dba::query($sql);

        return $id ? dba::fetch_assoc() : dba::fetch_assoc_all();
    }

    public static function create($user_id, $name)
    {
        $id = self::get_last_category_id($user_id);
        $id = $id + 1;

        $sql = "INSERT INTO  " . self::$table . "
                    ( `id`, `user_id`, `name`)
                VALUES
                    ($id,  $user_id, '$name')";
        dba::query($sql);

        return self::get($user_id, $id);
    }


    public static function update($user_id, $id, $name, $is_hidden)
    {
        $is_hidden = intval($is_hidden);
        $sql = "UPDATE " . self::$table . " SET `name` = '$name', `is_hidden` = '$is_hidden' WHERE `user_id` = $user_id and `id` = $id;";

        dba::query($sql);

        return self::get($user_id, $id);
    }


    public static function delete($user_id, $id)
    {
        $sql = "DELETE FROM " . self::$table . " WHERE `user_id` = $user_id and `id` = $id";

        return dba::query($sql);
    }

    private static function get_last_category_id($user_id)
    {
        $sql = "SELECT `id` FROM " . self::$table . " WHERE `user_id` = $user_id ORDER BY `id` DESC;";
        dba::query($sql);
        $last_operation = dba::fetch_assoc()['id'];

        return $last_operation ? $last_operation : 0;
    }

    public static function delete_all($user_id)
    {
        $sql = "DELETE FROM " . self::$table . " WHERE `user_id` = '$user_id'";
        dba::query($sql);

        return true;
    }
}
