<?php

/**
 * class worker
 * отвечает за актуализацию задач для worker
 */

namespace services;

use DateTime;
use model\watcher_model as model;

class watcher
{
    public static function run()
    {
        $watcher_id = md5(strval(random_int(1000, 9999) * random_int(1000, 9999)));
        $lock_name = 'watcher';
        $is_lock_success = std_set_lock($lock_name, $watcher_id);

        while (!$is_lock_success) {
            time_nanosleep(0, 1000000);
            $is_lock_success = std_set_lock($lock_name, $watcher_id);
        }

        if (!std_get_lock_content($lock_name) || std_get_lock_content($lock_name) != $watcher_id) {
            return;
        }

        $current_date = self::get_date_object();
        $last_operation = model::get_last_operation();

        if (!$last_operation) {
            $last_operation = 0;
            model::add_operation($current_date);
            std_remove_lock($lock_name);
            return;
        }

        self::check_time_diff(json_decode($last_operation['target_date'], true), $current_date);
        std_remove_lock($lock_name);
    }

    private static function check_time_diff($old_date, $new_date)
    {
        $old_timestamp = $old_date['timestamp'];
        $new_timestamp = $new_date['timestamp'];

        if (($new_timestamp - $old_timestamp) < 60) {
            return;
        }

        while ($old_timestamp < $new_timestamp - 1) {
            $old_timestamp += 60;
            $target_date = self::get_date_object($old_timestamp);
            model::add_operation($target_date);
        }
    }


    private static function get_date_object($timestamp = null)
    {
        if (!$timestamp) {
            $date = new DateTime();
            $timestamp = $date->getTimestamp();
        }

        $date_object = [];

        $date_object['timestamp'] = $timestamp - gmdate("s", $timestamp);
        $date_object['first_day'] = '01';
        $date_object['last_day'] = gmdate("t", $timestamp);
        $date_object['current_day'] = gmdate("d", $timestamp);
        $date_object['day_of_week'] = gmdate("w", $timestamp) == '0' ? '7' : gmdate("w", $timestamp);
        $date_object['current_time'] = gmdate('H:i', $timestamp);
        $date_object['current_date'] = gmdate('m.d.Y', $timestamp);

        return $date_object;
    }
}
