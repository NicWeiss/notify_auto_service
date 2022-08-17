<?php

namespace model;

use lib\DB;

final class SessionModel
{
    public static function get_all($user_id)
    {
        $sesions = TABLE_OF_SESSIONS;

        $sql = "SELECT * FROM $sesions WHERE `user_id` = '$user_id'";
        $sessions = DB::fetch_assoc_all($sql);

        foreach ($sessions as $key => $session) {
            $sessions[$key]['client'] = json_decode($session['client'], true);
            $sessions[$key]['location'] = json_decode($session['location'], true);
        }


        return $sessions;
    }

    public static function get_session($user_id, $session_id)
    {
        $sesions = TABLE_OF_SESSIONS;

        $sql = "SELECT * FROM $sesions WHERE `user_id` = '$user_id' and `id`='$session_id';";
        DB::query($sql);

        $session = DB::fetch_assoc();
        $session['client'] = json_decode($session['client'], true);
        $session['location'] = json_decode($session['location'], true);

        return $session;
    }


    public static function delete_session($user_id, $session_id)
    {
        $sesions = TABLE_OF_SESSIONS;

        $sql = "DELETE FROM $sesions WHERE `user_id` = '$user_id' and `id`='$session_id';";
        DB::query($sql);

        return DB::fetch_assoc();
    }


    public static function delete_all($user_id)
    {
        $table = TABLE_OF_SESSIONS;

        $sql = "DELETE FROM $table WHERE `user_id` = '$user_id'";
        DB::query($sql);

        return true;
    }
}
