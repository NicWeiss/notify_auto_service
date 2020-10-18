<?php

namespace model;

use lib\dba as dba;

final class acceptor_model
{
    public static function get_all()
    {
        $table = TABLE_OF_ACCEPTORS;
    }

    public static function create_acceptor($acceptor, $user) {
        $table = TABLE_OF_ACCEPTORS;
        $sql = "INSERT INTO  $table ( `user_id`, `name`, `system_id`, `account`, `status`) 
                VALUES ('$user[id]',  '$acceptor[name]', '$acceptor[system]', '$acceptor[account]', true )";
        dba:: query($sql);

        $sql = "SELECT * FROM $table  ORDER BY `id` DESC Limit 1;";
        dba:: query($sql);
        return dba::fetch_assoc();
    }

    public static function get_acceptors($user) {
        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;
        $sql = "SELECT a.id, a.name, a.system_id, a.account, a.status, s.type 
                    FROM $acceptor a
                    left join $system s on s.id = a.system_id
                    WHERE a.user_id= '$user[id]';";
        dba:: query($sql);
        return dba::fetch_assoc_all();
    }
}
