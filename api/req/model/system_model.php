<?php

namespace model;

use lib\dba as dba;

final class system_model
{
    public static function get_all()
    {
        $table = TABLE_OF_SYSTEMS;
        $sql = "SELECT * FROM  $table";
        dba:: query($sql);
        return dba::fetch_assoc_all();
    }

    public static function get_system($system_id) {
        $system = TABLE_OF_SYSTEMS;

        $sql = "SELECT * FROM $system  WHERE `id` = $system_id;";
        dba:: query($sql);
        return dba::fetch_assoc();
    }
}
