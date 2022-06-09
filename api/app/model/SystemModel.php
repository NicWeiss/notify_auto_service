<?php

namespace model;

use lib\dba as dba;

final class SystemModel
{
    public static function get_all()
    {
        $table = TABLE_OF_SYSTEMS;
        $sql = "SELECT * FROM  $table";

        return dba::fetch_assoc_all($sql);
    }

    public static function get_system($system_id)
    {
        $system = TABLE_OF_SYSTEMS;

        $sql = "SELECT * FROM $system  WHERE `id` = $system_id;";
        dba::query($sql);
        return dba::fetch_assoc();
    }

    public static function delete_all($user_id)
    {
        $table = TABLE_OF_SYSTEMS;

        $sql = "DELETE FROM $table WHERE `user_id` = '$user_id'";
        dba::query($sql);

        return true;
    }
}
