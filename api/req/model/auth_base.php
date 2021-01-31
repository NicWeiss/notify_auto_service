<?php

namespace model;

use lib\dba as dba;

final class auth_base
{
    public static function create_user($data)
    {
        $DB = TABLE_OF_USERS;
        $sql = "INSERT INTO  $DB
           ( `user`, `email`, `password`)
            VALUES ('" . $data['user'] . "',
                    '" . $data['email'] . "',
                    '" . $data['password'] . "')";
        dba::query($sql);
        $sql = "SELECT `id` FROM  $DB  WHERE `email`='" . $data['email'] . "'";
        dba::query($sql);
        $res = dba::fetch_assoc();
        return $res['id'];
    }

    public static function update_password($email, $password)
    {
        $DB = TABLE_OF_USERS;
        $sql = "UPDATE $DB SET `password` = '$password'
            WHERE `email` = '$email'";
        return dba::query($sql);
    }

    public static function check_user_exists($email)
    {
        $DB = TABLE_OF_USERS;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "'";
        dba::query($sql);
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
        dba::query($sql);
    }

    public static function create_code($data)
    {
        $DB = TABLE_OF_REG_CODES;
        $sql = "INSERT INTO  $DB
           ( `email`, `code`, `expire_at`)
            VALUES ('" . $data['email'] . "',
                    '" . $data['code'] . "',
                    '" . $data['expire_at'] . "')";
        dba::query($sql);
    }

    public static function create_restore_code($data)
    {
        $DB = TABLE_OF_RESTORE_CODES;
        $sql = "INSERT INTO  $DB
           ( `email`, `code`, `expire_at`)
            VALUES ('" . $data['email'] . "',
                    '" . $data['code'] . "',
                    '" . $data['expire_at'] . "')";
        dba::query($sql);
    }

    public static function is_reg_code_exist($email, $current)
    {
        $DB = TABLE_OF_REG_CODES;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and expire_at > '" . $current . "'";
        dba::query($sql);
        $res = dba::fetch_assoc();
        if (!is_array($res)) return false;
        return count($res) > 0 ? true : false;
    }

    public static function is_restore_code_exist($email, $current)
    {
        $DB = TABLE_OF_RESTORE_CODES;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and expire_at > '" . $current . "'";
        dba::query($sql);
        $res = dba::fetch_assoc();
        if (!is_array($res)) return false;
        return count($res) > 0 ? true : false;
    }

    public static function is_restore_code_exist_by_code($code, $current)
    {
        $DB = TABLE_OF_RESTORE_CODES;
        $sql = "SELECT * FROM  $DB  WHERE code='" . $code . "' and expire_at > '" . $current . "'";
        dba::query($sql);
        $res = dba::fetch_assoc();
        if (!is_array($res)) return false;
        return count($res) > 0 ? true : false;
    }

    public static function get_email_by_restore_code($code, $current)
    {
        $DB = TABLE_OF_RESTORE_CODES;
        $sql = "SELECT * FROM  $DB  WHERE code='" . $code . "' and expire_at > '" . $current . "'";
        dba::query($sql);
        $res = dba::fetch_assoc();
        if (!is_array($res)) return false;
        return count($res) > 0 ? $res['email'] : false;
    }

    public static function get_reg_code($email, $current)
    {
        $DB = TABLE_OF_REG_CODES;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and expire_at > '" . $current . "'";
        dba::query($sql);
        $res = dba::fetch_assoc();
        return $res['code'];
    }

    public static function get_user($email, $password)
    {
        $DB = TABLE_OF_USERS;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and password = '" . $password . "'";
        dba::query($sql);
        $res = dba::fetch_assoc();
        return $res;
    }

    public static function get_user_by_session($user_session_id)
    {
        $sesions = TABLE_OF_SESSIONS;
        $users = TABLE_OF_USERS;

        $sql = "SELECT u.user, u.email, u.id, s.session FROM $users u
            left join $sesions s on s.user_id = u.id
            WHERE s.session = '$user_session_id'";

        dba::query($sql);
        return dba::fetch_assoc();
    }
}
