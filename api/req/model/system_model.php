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
}
