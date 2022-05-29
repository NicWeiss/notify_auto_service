<?php

namespace model;

use lib\dba as dba;

final class UserModel
{
    public static $table = TABLE_OF_USERS;


    public static function get($user_id)
    {
        $sql = "SELECT `id`, `name`, `email` FROM  " . self::$table  . " where `id` = $user_id";
        dba::query($sql);

        return dba::fetch_assoc();
    }


    public static function update($user_id, $data)
    {
        $sql = "UPDATE " . self::$table . " SET
           `name` = '" . $data['name'] . "',
           `email` = '" . $data['email'] . "',
           `timezone` = '" . $data['timezone'] . "'
            where `id` = $user_id";
        dba::query($sql);

        return self::get($user_id);
    }


    public static function delete($user_id)
    {
        $sql = "DELETE FROM " . self::$table . " WHERE `id` = $user_id";
        return dba::query($sql);
    }
}
