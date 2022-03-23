<?php

namespace model;

use lib\dba as dba;
use lib\request;

final class acceptor_model
{
    public static function get_all()
    {
        $table = TABLE_OF_ACCEPTORS;
    }

    public static function create_acceptor($acceptor, $user)
    {
        $table = TABLE_OF_ACCEPTORS;
        $sql = "INSERT INTO  $table ( `user_id`, `name`, `system_id`, `account`, `status`)
                VALUES ('$user[id]',  '$acceptor[name]', '$acceptor[system_id]', '$acceptor[account]', true )";
        dba::query($sql);

        $sql = "SELECT * FROM $table  ORDER BY `id` DESC Limit 1;";
        dba::query($sql);
        return dba::fetch_assoc();
    }

    public static function get_acceptors($user, $page, $per_page)
    {
        $limit = '';
        $status =  request::$query_params['status'];

        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;

        if ($page && $per_page) {
            $offset = ($page - 1) * $per_page;
            $limit = "LIMIT $offset, $per_page";
        }

        $status = $status ? " and a.status= '$status'" : null;

        $sql = "SELECT a.id, a.name, a.system_id, a.account, a.status, s.type
                    FROM $acceptor a
                    left join $system s on s.id = a.system_id
                    WHERE a.user_id= '$user[id] ' $status ORDER BY id DESC " . $limit . ";";
        dba::query($sql);
        return dba::fetch_assoc_all();
    }

    public static function get_total($user)
    {
        $acceptor = TABLE_OF_ACCEPTORS;

        $sql = "SELECT COUNT(id) FROM $acceptor  WHERE user_id= '$user[id]';";
        dba::query($sql);
        $notify_count = dba::fetch_assoc();
        return $notify_count['COUNT(id)'];
    }

    public static function delete_acceptor($entity_id, $user)
    {
        $table = TABLE_OF_ACCEPTORS;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $sql = "DELETE FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba::query($sql);
        $sql = "DELETE FROM $notify_acceptors WHERE `acceptor_id`= '$entity_id'";
        dba::query($sql);

        return true;
    }

    public static function get_acceptor($acceptor_id, $user)
    {
        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;

        $sql = "SELECT a.id, a.name, a.system_id, a.account, a.status, s.type
                    FROM $acceptor a
                    left join $system s on s.id = a.system_id
                    WHERE a.id = $acceptor_id and a.user_id= '$user[id]';";
        dba::query($sql);
        $acceptor = dba::fetch_assoc();

        return $acceptor;
    }

    public static function update_acceptor($entity_id, $acceptor, $user)
    {
        $table = TABLE_OF_ACCEPTORS;

        $sql = "UPDATE $table SET `name`='$acceptor[name]', `system_id`='$acceptor[system_id]',
                 `account`='$acceptor[account]', `status`='$acceptor[status]'
                WHERE `id`= '$entity_id' and `user_id` = '$user[id]'
                ";
        dba::query($sql);

        $sql = "SELECT * FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba::query($sql);
        $acceptor = dba::fetch_assoc();


        return $acceptor;
    }
}
