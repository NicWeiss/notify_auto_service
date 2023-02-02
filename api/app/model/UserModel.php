<?php

namespace model;

use lib\DB;

final class UserModel
{
    public static $table = TABLE_OF_USERS;



    public static function check_user_exists($email)
    {
        $DB = TABLE_OF_USERS;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "'";
        DB::query($sql);
        $res = DB::fetch_assoc();
        if (!is_array($res)) return false;
        return count($res) > 0 ? true : false;
    }

    public static function create_user($data)
    {
        $DB = TABLE_OF_USERS;
        $sql = "INSERT INTO  $DB
           ( `name`, `email`, `password`)
            VALUES ('" . $data['name'] . "',
                    '" . $data['email'] . "',
                    '" . $data['password'] . "')";
        DB::query($sql);
        $sql = "SELECT * FROM  $DB  WHERE `email`='" . $data['email'] . "'";
        DB::query($sql);
        $res = DB::fetch_assoc();
        return $res;
    }

    public static function get($user_id)
    {
        $sql = "SELECT `id`, `name`, `email` FROM  " . self::$table  . " where `id` = $user_id";
        DB::query($sql);

        return DB::fetch_assoc();
    }

    public static function get_by_credentials($email, $password)
    {
        $DB = TABLE_OF_USERS;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and password = '" . $password . "'";
        DB::query($sql);
        $res = DB::fetch_assoc();

        return $res;
    }

    public static function get_by_session($user_session_id)
    {
        $sesions = TABLE_OF_SESSIONS;
        $users = TABLE_OF_USERS;

        $sql = "SELECT u.name, u.email, u.id, s.session, u.password FROM $users u
            left join $sesions s on s.user_id = u.id
            WHERE s.session = '$user_session_id'";

        DB::query($sql);

        return DB::fetch_assoc();
    }

    public static function update($user_id, $data)
    {
        $sql = "UPDATE " . self::$table . " SET
           `name` = '" . $data['name'] . "',
           `email` = '" . $data['email'] . "',
           `timezone` = '" . $data['timezone'] . "'
            where `id` = $user_id";
        DB::query($sql);

        return self::get($user_id);
    }

    public static function update_password($email, $password)
    {
        $DB = TABLE_OF_USERS;
        $sql = "UPDATE $DB SET `password` = '$password'
            WHERE `email` = '$email'";
        return DB::query($sql);
    }

    public static function delete($user_id)
    {
        $sql = "DELETE FROM " . self::$table . " WHERE `id` = $user_id";
        return DB::query($sql);
    }
}
