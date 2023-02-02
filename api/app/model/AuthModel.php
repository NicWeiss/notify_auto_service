<?php

namespace model;

use lib\DB as DB;

final class AuthModel
{

    private static $SIX_DAYS = 518400;
    private static $ONE_WEEK = 604800;


    public static function set_session($data)
    {
        $DB = TABLE_OF_SESSIONS;
        $sql = "INSERT INTO  $DB
           ( `user_id`, `session`, `expire_at`, `client`, `location`)
            VALUES ('" . $data['user_id'] . "',
                    '" . $data['session'] . "',
                    '" . $data['expire_at'] . "',
                    '" . $data['client'] . "',
                    '" . $data['location'] . "')";
        DB::query($sql);
    }

    public static function create_code($data)
    {
        $DB = TABLE_OF_REG_CODES;
        $sql = "INSERT INTO  $DB
           ( `email`, `code`, `expire_at`)
            VALUES ('" . $data['email'] . "',
                    '" . $data['code'] . "',
                    '" . $data['expire_at'] . "')";
        DB::query($sql);
    }

    public static function create_restore_code($data)
    {
        $DB = TABLE_OF_RESTORE_CODES;
        $sql = "INSERT INTO  $DB
           ( `email`, `code`, `expire_at`)
            VALUES ('" . $data['email'] . "',
                    '" . $data['code'] . "',
                    '" . $data['expire_at'] . "')";
        DB::query($sql);
    }

    public static function is_reg_code_exist($email, $current)
    {
        $DB = TABLE_OF_REG_CODES;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and expire_at > '" . $current . "'";
        DB::query($sql);
        $res = DB::fetch_assoc();

        if (!is_array($res)) return false;

        return count($res) > 0 ? true : false;
    }

    public static function is_restore_code_exist($email, $current)
    {
        $DB = TABLE_OF_RESTORE_CODES;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and expire_at > '" . $current . "'";
        DB::query($sql);
        $res = DB::fetch_assoc();

        if (!is_array($res)) return false;

        return count($res) > 0 ? true : false;
    }

    public static function is_restore_code_exist_by_code($code, $current)
    {
        $DB = TABLE_OF_RESTORE_CODES;
        $sql = "SELECT * FROM  $DB  WHERE code='" . $code . "' and expire_at > '" . $current . "'";
        DB::query($sql);
        $res = DB::fetch_assoc();

        if (!is_array($res)) return false;

        return count($res) > 0 ? true : false;
    }

    public static function get_email_by_restore_code($code, $current)
    {
        $DB = TABLE_OF_RESTORE_CODES;
        $sql = "SELECT * FROM  $DB  WHERE code='" . $code . "' and expire_at > '" . $current . "'";
        DB::query($sql);
        $res = DB::fetch_assoc();

        if (!is_array($res)) return false;

        return count($res) > 0 ? $res['email'] : false;
    }

    public static function get_reg_code($email, $current)
    {
        $DB = TABLE_OF_REG_CODES;
        $sql = "SELECT * FROM  $DB  WHERE email='" . $email . "' and expire_at > '" . $current . "'";
        DB::query($sql);
        $res = DB::fetch_assoc();

        return $res['code'];
    }

    public static function is_session_valid($session)
    {
        $now = date_timestamp_get(date_create());
        $DB = TABLE_OF_SESSIONS;

        $sql = "SELECT * FROM  $DB WHERE `session`='$session' and `expire_at` > $now";
        DB::query($sql);
        $session = DB::fetch_assoc();

        if ($session) {
            self::try_regenerate_session($session);
        }

        return $session ? true : false;
    }

    private static function try_regenerate_session($session)
    {
        $date = date_create();
        $current_timstamp = date_timestamp_get($date);
        $session_expire_at = $session['expire_at'];

        if (($session_expire_at - $current_timstamp) < self::$SIX_DAYS) {
            $session_table = TABLE_OF_SESSIONS;
            $id = $session['id'];
            $new_expire_date = $current_timstamp + self::$ONE_WEEK;

            $sql = "UPDATE $session_table SET `expire_at` = '$new_expire_date'
                WHERE `id` = '$id'";
            DB::query($sql);
        }
    }

    public static function delete_outdated_sessions()
    {
        $session_table = TABLE_OF_SESSIONS;
        $date = date_create();
        $current_timstamp = date_timestamp_get($date);

        $sql = "DELETE FROM $session_table WHERE `expire_at` < $current_timstamp";
        DB::query($sql);
    }
}
