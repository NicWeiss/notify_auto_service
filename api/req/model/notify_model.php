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

    public static function get_acceptors_by_notify_id($id, $status = null) {
        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $status = $status ? " and a.status= '$status'" : null;

        $sql = "SELECT a.id, a.name, a.system_id, a.account, a.status, s.type
                    FROM $notify_acceptors na
                    left join $acceptor a on a.id = na.acceptor_id
                    left join $system s on s.id = a.system_id
                    where na.notify_id = '$id' $status ORDER BY `id` ;";
        dba:: query($sql);
        return dba::fetch_assoc_all();
    }

    public static function get_all_notify($user) {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT * FROM $notify  WHERE user_id= '$user[id]';";
        dba:: query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = self::get_acceptors_by_notify_id($notify_list[$key]['id']);
        }

        return $notify_list;
    }

    public static function update_notify($entity_id, $notify, $user) {
        $table = TABLE_OF_NOTIFY;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $acceptors = explode(',', $notify['acceptors']);

        $notify['day_of_week'] = array_key_exists("day_of_week", $notify) ?  $notify["day_of_week"] : '';
        $notify['text'] = array_key_exists("text", $notify) ?  $notify["text"] : '';
        $notify['time'] = array_key_exists("time", $notify) ?  $notify["time"] : '';

        $sql = "UPDATE $table SET `name`='$notify[name]', `text`='$notify[text]', `periodic`='$notify[periodic]', 
                 `day_of_week`='$notify[day_of_week]', `date`='$notify[date]', `time`='$notify[time]', `status`=$notify[status]
                WHERE `id`= '$entity_id' and `user_id` = '$user[id]'
                ";
        dba:: query($sql);

        $sql = "SELECT * FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba:: query($sql);
        $notify = dba::fetch_assoc();

        $sql = "DELETE FROM $notify_acceptors WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba:: query($sql);

        foreach ($acceptors as $acceptor_idr) {
            $sql = "INSERT INTO  $notify_acceptors ( `notify_id`, `acceptor_id`) 
                    VALUES ('$notify[id]',  '$acceptor_idr')";
            dba:: query($sql);
        }

        $notify['acceptorsList'] = self::get_acceptors_by_notify_id($notify['id']);

        return $notify;
    }

    public static function delete_notify($entity_id, $user) {
        $table = TABLE_OF_NOTIFY;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $sql = "DELETE FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba:: query($sql);
        $sql = "DELETE FROM $notify_acceptors WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba:: query($sql);

        return true;
    }

    public static function get_notify_for_cron() {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT * FROM $notify  WHERE status= '1';";
        dba:: query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = self::get_acceptors_by_notify_id($value['id'], '1');
        }

        return $notify_list;
    }

}
