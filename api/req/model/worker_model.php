<?php

namespace model;

use lib\dba as dba;
use model\notify_model as notify;

final class worker_model
{

    public static function get_once_notifies($date, $time)
    {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT * FROM $notify  WHERE status= '1' and `periodic` = 'once'
            and `date` = '$date' and `time` = '$time';";
        dba::query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = notify::get_acceptors_by_notify_id($value['id'], '1');
        }

        return $notify_list;
    }

    public static function get_types_notifies($type, $time)
    {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT * FROM $notify  WHERE status= '1' and `periodic` = '$type'
            and `time` = '$time';";
        dba::query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = notify::get_acceptors_by_notify_id($value['id'], '1');
        }

        return $notify_list;
    }

    public static function get_day_of_week_notifies($time, $day_of_week)
    {
        $notify = TABLE_OF_NOTIFY;

        $sql = "SELECT * FROM $notify  WHERE status= '1' and `periodic` = 'day_of_week'
            and `time` = '$time' and `day_of_week` = '$day_of_week';";
        dba::query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = notify::get_acceptors_by_notify_id($value['id'], '1');
        }

        return $notify_list;
    }

    public static function get_every_month_notify($date, $time)
    {
        $notify = TABLE_OF_NOTIFY;

        $date = substr($date, 7, 3);

        $sql = "SELECT * FROM $notify  WHERE status= '1' and `periodic` = 'every_month'
            and `time` = '$time' and `date` like '%$date';";
        dba::query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = notify::get_acceptors_by_notify_id($value['id'], '1');
        }

        return $notify_list;
    }

    public static function get_every_year_notify($date, $time)
    {
        $notify = TABLE_OF_NOTIFY;

        $date = substr($date, 4, 6);

        $sql = "SELECT * FROM $notify  WHERE status= '1' and `periodic` = 'every_year'
            and `time` = '$time' and `date` like '%$date';";
        dba::query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            $notify_list[$key]['acceptorsList'] = notify::get_acceptors_by_notify_id($value['id'], '1');
        }

        return $notify_list;
    }
}
