<?php

namespace model;

use lib\DB as DB;
use lib\Request;

final class AcceptorModel
{

    public static function create_acceptor($acceptor, $user)
    {
        $table = TABLE_OF_ACCEPTORS;
        $sql = "INSERT INTO  $table ( `user_id`, `name`, `system_id`, `account`, `status`, `is_system`)
                VALUES ('$user[id]',  '$acceptor[name]', '$acceptor[system_id]', '$acceptor[account]', true, $acceptor[is_system])";
        DB::query($sql);

        $sql = "SELECT * FROM $table  ORDER BY `id` DESC Limit 1;";

        return DB::fetch_assoc($sql);
    }

    public static function get_acceptors($user, $page, $per_page)
    {
        $limit = '';
        $status =  Request::$query_params['status'];

        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;

        if ($page && $per_page) {
            $offset = ($page - 1) * $per_page;
            $limit = "LIMIT $offset, $per_page";
        }

        $status = $status ? " and a.status= '$status'" : null;

        $sql = "SELECT a.id, a.name, a.system_id, a.account, a.status, s.type, a.is_system
                    FROM $acceptor a
                    left join $system s on s.id = a.system_id
                    WHERE a.user_id= '$user[id]' $status ORDER BY id DESC " . $limit . ";";

        return DB::fetch_assoc_all($sql);
    }

    public static function get_total($user)
    {
        $acceptor = TABLE_OF_ACCEPTORS;

        $sql = "SELECT COUNT(id) FROM $acceptor  WHERE user_id= '$user[id]';";
        DB::query($sql);
        $notify_count = DB::fetch_assoc();
        return $notify_count['COUNT(id)'];
    }

    public static function delete_acceptor($entity_id, $user)
    {
        $table = TABLE_OF_ACCEPTORS;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $sql = "DELETE FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        DB::query($sql);
        $sql = "DELETE FROM $notify_acceptors WHERE `acceptor_id`= '$entity_id'";
        DB::query($sql);

        return true;
    }

    public static function get_acceptor($acceptor_id, $user)
    {
        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;

        $sql = "SELECT a.id, a.name, a.system_id, a.account, a.status, s.type, a.is_system
                    FROM $acceptor a
                    left join $system s on s.id = a.system_id
                    WHERE a.id = $acceptor_id and a.user_id= '$user[id]';";
        DB::query($sql);
        $acceptor = DB::fetch_assoc();

        return $acceptor;
    }

    public static function get_one_acceptor_by_system($system_id, $user)
    {
        $acceptor = TABLE_OF_ACCEPTORS;

        $sql = "SELECT * FROM $acceptor WHERE `system_id` = $system_id and `user_id`= '$user[id]';";
        DB::query($sql);
        $acceptor = DB::fetch_assoc();

        return $acceptor;
    }

    public static function update_acceptor($entity_id, $acceptor, $user)
    {
        $table = TABLE_OF_ACCEPTORS;

        $sql = "UPDATE $table SET `name`='$acceptor[name]', `system_id`='$acceptor[system_id]',
                 `account`='$acceptor[account]', `status`='$acceptor[status]'
                WHERE `id`= '$entity_id' and `user_id` = '$user[id]'
                ";
        DB::query($sql);

        $sql = "SELECT * FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        DB::query($sql);
        $acceptor = DB::fetch_assoc();


        return $acceptor;
    }

    public static function delete_all($user_id)
    {
        $table = TABLE_OF_ACCEPTORS;

        $sql = "DELETE FROM $table WHERE `user_id` = '$user_id'";
        DB::query($sql);

        return true;
    }
}
