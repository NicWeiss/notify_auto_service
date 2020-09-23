<?php

namespace model;

use lib\dba as dba;

final class base
{
    public static function create_user($data)
    {
        $DB = TABLE_OF_USERS;
        $sql = "INSERT INTO  $DB  
           ( `user`, `email`, `password`) 
            VALUES ('" . $data['user'] . "', 
                    '" . $data['email'] . "', 
                    '" . $data['password'] . "')";
        dba:: query($sql);
        $sql = "SELECT `id` FROM  $DB  WHERE `email`='" . $data['email'] . "'";
        dba:: query($sql);
        $res = dba::fetch_assoc();
        return $res['id'];
    }

    public static function check_user_exists($email)
    {
        $DB = TABLE_OF_USERS;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "'";
        dba:: query($sql);
        $res = dba::fetch_assoc();
        if (!is_array($res)) return false;
        return count($res) > 0 ? true : false;
    }

    public static function set_session($data)
    {
        $DB = TABLE_OF_SESSIONS;
        $sql = "INSERT INTO  $DB  
           ( `user_id`, `session`, `expire_at`) 
            VALUES ('" . $data['user_id'] . "', 
                    '" . $data['session'] . "', 
                    '" . $data['expire_at'] . "')";
        dba:: query($sql);
    }

    public static function create_code($data)
    {
        $DB = TABLE_OF_REG_CODES;
        $sql = "INSERT INTO  $DB  
           ( `email`, `code`, `expire_at`) 
            VALUES ('" . $data['email'] . "', 
                    '" . $data['code'] . "', 
                    '" . $data['expire_at'] . "')";
        dba:: query($sql);
    }

    public static function is_reg_code_exist($email, $current)
    {
        $DB = TABLE_OF_REG_CODES;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and expire_at > '" . $current . "'";
        dba:: query($sql);
        $res = dba::fetch_assoc();
        if (!is_array($res)) return false;
        return count($res) > 0 ? true : false;
    }
}
