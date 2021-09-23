<?php

namespace model;

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
        $id = md5(uniqid(rand(), true));

        $sql = "INSERT INTO  " . self::$table . "
                    ( `id`, `user_id`, `name`)
                VALUES
                    ('$id',  $user_id, '$name')";
        dba::query($sql);

        return self::get($id);
    }


    public static function update($user_id, $id, $name)
    {
        $sql = "UPDATE " . self::$table . " SET `name` = '$name' WHERE `user_id` = $user_id and `id` = '$id';";
        dba::query($sql);

        return self::get($id);
    }


    public static function delete($user_id, $id)
    {
        $sql = "DELETE FROM " . self::$table . " WHERE `user_id` = $user_id and `id` != $id";

        return dba::query($sql);
    }
}
