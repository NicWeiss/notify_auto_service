<?php

namespace model;

use lib\DB as DB;

final class SystemModel
{
    public static function get_all()
    {
        $table = TABLE_OF_SYSTEMS;
        $sql = "SELECT * FROM  $table where `is_system` = false";

        return DB::fetch_assoc_all($sql);
    }

    public static function get_system($system_id)
    {
        $system = TABLE_OF_SYSTEMS;

        $sql = "SELECT * FROM $system  WHERE `id` = $system_id;";
        DB::query($sql);
        return DB::fetch_assoc();
    }

    public static function delete_all($user_id)
    {
        $table = TABLE_OF_SYSTEMS;

        $sql = "DELETE FROM $table WHERE `user_id` = '$user_id'";
        DB::query($sql);

        return true;
    }

    public static function get_system_by_type($type)
    {
        $system = TABLE_OF_SYSTEMS;

        $sql = "SELECT * FROM $system  WHERE `type` = '$type';";
        DB::query($sql);

        return DB::fetch_assoc();
    }
}
