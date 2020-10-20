<?php

namespace model;

use control\acceptor;
use lib\dba as dba;

final class notify_model
{

    public static function create_notify($notify, $user) {
        $table = TABLE_OF_NOTIFY;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $acceptors = explode(',', $notify['acceptors']);

        $notify['day_of_week'] = array_key_exists("day_of_week", $notify) ?  $notify["day_of_week"] : '';
        $notify['text'] = array_key_exists("text", $notify) ?  $notify["text"] : '';
        $notify['time'] = array_key_exists("time", $notify) ?  $notify["time"] : '';

        $sql = "INSERT INTO  $table ( `user_id`, `name`, `text`, `periodic`, `day_of_week`, `date`, `time`) 
                VALUES ('$user[id]',  '$notify[name]', '$notify[text]', '$notify[periodic]', '$notify[day_of_week]', '$notify[date]', '$notify[time]' )";
        dba:: query($sql);

        $sql = "SELECT * FROM $table  ORDER BY `id` DESC Limit 1;";
        dba:: query($sql);
        $notify = dba::fetch_assoc();

        foreach ($acceptors as $acceptor_idr) {
            $sql = "INSERT INTO  $notify_acceptors ( `notify_id`, `acceptor_id`) 
                    VALUES ('$notify[id]',  '$acceptor_idr')";
            dba:: query($sql);
        }

        $notify['acceptorsList'] = self::get_acceptors_by_notify_id($notify['id']);
        return $notify;
    }

    public static function get_acceptors_by_notify_id($id) {
        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $sql = "SELECT a.id, a.name, a.system_id, a.account, a.status, s.type
                    FROM $notify_acceptors na
                    left join $acceptor a on a.id = na.acceptor_id
                    left join $system s on s.id = a.system_id
                    where `notify_id` = '$id'  ORDER BY `id`;";
        dba:: query($sql);
        return dba::fetch_assoc_all();
    }

    public static function get_all_notify($user) {
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;
        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT * FROM $notify  WHERE user_id= '$user[id]';";
        dba:: query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = self::get_acceptors_by_notify_id($notify_list[$key]['id']);
        }

        return $notify_list;
    }
}
