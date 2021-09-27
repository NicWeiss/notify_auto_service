<?php

namespace model;

use control\acceptor;
use lib\dba as dba;

final class notify_model
{

    public static function create_notify($notify, $user)
    {
        $table = TABLE_OF_NOTIFY;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $acceptors = $notify['acceptorsList'];

        $notify['day_of_week'] = array_key_exists("day_of_week", $notify) ?  $notify["day_of_week"] : '';
        $notify['text'] = array_key_exists("text", $notify) ?  $notify["text"] : '';
        $notify['time'] = array_key_exists("time", $notify) ?  $notify["time"] : '';
        $notify['category_id'] = array_key_exists("category_id", $notify) ?  $notify["category_id"] : 0;

        $sql = "INSERT INTO  $table ( `user_id`, `name`, `text`, `periodic`, `day_of_week`,
                                      `date`, `time`, `category_id`)
                VALUES ('$user[id]',  '$notify[name]', '$notify[text]', '$notify[periodic]',
                        '$notify[day_of_week]', '$notify[date]', '$notify[time]' , $notify[category_id])";
        dba::query($sql);

        $sql = "SELECT * FROM $table  ORDER BY `id` DESC Limit 1;";
        dba::query($sql);
        $notify = dba::fetch_assoc();

        foreach ($acceptors as $acceptor) {
            $sql = "INSERT INTO  $notify_acceptors ( `notify_id`, `acceptor_id`)
                    VALUES ('$notify[id]',  '" . $acceptor['id'] . "')";
            dba::query($sql);
        }

        $notify['acceptorsList'] = self::get_acceptors_by_notify_id($notify['id']);
        return $notify;
    }

    public static function get_acceptors_by_notify_id($id, $status = 1)
    {
        $acceptor = TABLE_OF_ACCEPTORS;
        $system = TABLE_OF_SYSTEMS;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $status = $status ? " and a.status= '$status'" : null;

        $sql = "SELECT a.id, a.name, a.system_id, a.account, a.status, s.type
                    FROM $notify_acceptors na
                    left join $acceptor a on a.id = na.acceptor_id
                    left join $system s on s.id = a.system_id
                    where na.notify_id = '$id' $status ORDER BY `id` ;";
        dba::query($sql);
        return dba::fetch_assoc_all();
    }

    public static function get_all_notify($user, $category_id, $page, $per_page)
    {
        $notify = TABLE_OF_NOTIFY;

        $offset = ($page - 1) * $per_page;

        $sql = "SELECT * FROM $notify  WHERE user_id= '$user[id]' AND category_id= '$category_id' ORDER BY id DESC  LIMIT $offset, $per_page;";
        dba::query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = self::get_acceptors_by_notify_id($notify_list[$key]['id']);
        }

        return $notify_list;
    }

    public static function get_total($user)
    {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT COUNT(id) FROM $notify  WHERE user_id= '$user[id]';";
        dba::query($sql);
        $notify_count = dba::fetch_assoc();
        return $notify_count['COUNT(id)'];
    }

    public static function get_notify($notify_id, $user)
    {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT * FROM $notify  WHERE `id` = $notify_id and `user_id`= '$user[id]';";
        dba::query($sql);
        $notify_list = dba::fetch_assoc();

        $notify_list['acceptorsList'] = self::get_acceptors_by_notify_id($notify_list['id']);

        return $notify_list;
    }

    public static function update_notify($entity_id, $notify, $user)
    {
        $table = TABLE_OF_NOTIFY;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;
        $acceptors = $notify['acceptorsList'];

        $notify['day_of_week'] = array_key_exists("day_of_week", $notify) ?  $notify["day_of_week"] : '';
        $notify['text'] = array_key_exists("text", $notify) ?  $notify["text"] : '';
        $notify['time'] = array_key_exists("time", $notify) ?  $notify["time"] : '';
        $notify['category_id'] = array_key_exists("category_id", $notify) ?  $notify["category_id"] : 0;

        $sql = "UPDATE $table SET `name`='$notify[name]', `text`='$notify[text]', `periodic`='$notify[periodic]',
                 `day_of_week`='$notify[day_of_week]', `date`='$notify[date]', `time`='$notify[time]', `category_id`=$notify[category_id],
                 `status`=$notify[status]
                WHERE `id`= '$entity_id' and `user_id` = '$user[id]'
                ";
        dba::query($sql);

        $sql = "SELECT * FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba::query($sql);
        $notify = dba::fetch_assoc();

        $sql = "DELETE FROM $notify_acceptors WHERE `notify_id`= '$entity_id'";
        dba::query($sql);

        foreach ($acceptors as $acceptor) {
            $sql = "INSERT INTO  $notify_acceptors ( `notify_id`, `acceptor_id`)
                    VALUES ('$notify[id]',  '" . $acceptor['id'] . "')";
            dba::query($sql);
        }

        $notify['acceptorsList'] = self::get_acceptors_by_notify_id($notify['id']);

        return $notify;
    }

    public static function delete_notify($entity_id, $user)
    {
        $table = TABLE_OF_NOTIFY;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;

        $sql = "DELETE FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba::query($sql);
        $sql = "DELETE FROM $notify_acceptors WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        dba::query($sql);

        return true;
    }
}
