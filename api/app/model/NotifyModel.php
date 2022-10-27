<?php

namespace model;

use lib\DB;

final class NotifyModel
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
        DB::query($sql);

        $sql = "SELECT * FROM $table  ORDER BY `id` DESC Limit 1;";
        DB::query($sql);
        $notify = DB::fetch_assoc();

        foreach ($acceptors as $acceptor) {
            $sql = "INSERT INTO  $notify_acceptors ( `notify_id`, `acceptor_id`)
                    VALUES ('$notify[id]',  '" . $acceptor['id'] . "')";
            DB::query($sql);
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

        return DB::fetch_assoc_all($sql);
    }

    public static function get_all_notify($user, $category_id, $page, $per_page)
    {
        $notify = TABLE_OF_NOTIFY;

        $offset = ($page - 1) * $per_page;
        $limit = $per_page ? "LIMIT $offset, $per_page" : "";

        $sql = "SELECT * FROM $notify  WHERE user_id= '$user[id]' AND category_id= '$category_id' ORDER BY id DESC $limit ;";
        $notify_list = DB::fetch_assoc_all($sql);

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = self::get_acceptors_by_notify_id($notify_list[$key]['id']);
        }

        return $notify_list;
    }

    public static function get_total($user)
    {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT COUNT(id) FROM $notify  WHERE user_id= '$user[id]';";
        DB::query($sql);
        $notify_count = DB::fetch_assoc();
        return $notify_count['COUNT(id)'];
    }

    public static function get_notify($notify_id, $user)
    {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT * FROM $notify  WHERE `id` = $notify_id and `user_id`= '$user[id]';";
        DB::query($sql);
        $notify_list = DB::fetch_assoc();

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

        if (!DB::query($sql)) {
            return false;
        }

        $sql = "SELECT * FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user[id]'";
        if (!DB::query($sql)) {
            return false;
        }
        $notify = DB::fetch_assoc();

        $sql = "DELETE FROM $notify_acceptors WHERE `notify_id`= '$entity_id'";
        if (!DB::query($sql)) {
            return false;
        }

        foreach ($acceptors as $acceptor) {
            $sql = "INSERT INTO  $notify_acceptors ( `notify_id`, `acceptor_id`)
                    VALUES ('$notify[id]',  '" . $acceptor['id'] . "')";
            if (!DB::query($sql)) {
                return false;
            }
        }

        $notify['acceptorsList'] = self::get_acceptors_by_notify_id($notify['id']);

        return $notify;
    }

    public static function delete_notify($entity_id, $user)
    {
        $table = TABLE_OF_NOTIFY;
        $notify_acceptors = TABLE_OF_NOTIFY_ACCEPTORS;
        $user_id = $user['id'];

        $sql = "DELETE FROM $table WHERE `id`= '$entity_id' and `user_id` = '$user_id'";
        DB::query($sql);
        $sql = "DELETE FROM $notify_acceptors WHERE `notify_id`= '$entity_id'";
        DB::query($sql);

        return true;
    }

    public static function move_notify($entity_id, $target_category_id, $user)
    {
        $table = TABLE_OF_NOTIFY;

        $sql = "UPDATE $table SET `category_id`= '$target_category_id' WHERE `id`='$entity_id' and `user_id` = '$user[id]'";
        DB::query($sql);

        return true;
    }

    public static function delete_by_category_id($category_id, $user)
    {
        $notify_list = self::get_all_notify($user, $category_id, 0, 0);

        foreach ($notify_list as $notify) {
            self::delete_notify($notify['id'], $user);
        }

        return true;
    }

    public static function move_notifies($from_category_id, $target_category_id, $user)
    {
        $notify_list = self::get_all_notify($user, $from_category_id, 0, 0);

        foreach ($notify_list as $notify) {
            self::move_notify($notify['id'], $target_category_id, $user);
        }

        return true;
    }

    public static function delete_all($user_id)
    {
        $table = TABLE_OF_NOTIFY;

        $sql = "DELETE FROM $table WHERE `user_id` = '$user_id'";
        DB::query($sql);

        return true;
    }
}
