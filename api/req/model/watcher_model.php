<?php

namespace model;

use lib\dba as dba;

final class watcher_model
{
    public static $operations = TABLE_OF_OPERATIONS;
    public static $lock_name = 'query';

    public static function add_operation($target_date)
    {
        $sql = "INSERT INTO " . self::$operations . " (`target_date`, `complete_date`, `type`)
      VALUES('" . json_encode($target_date) . "', '', 'wait')";

        return dba::query($sql);
    }

    public static function add_operations($operations)
    {
        $values = '';

        foreach ($operations as $operation) {
            if ($values != '') {
                $values = $values . ', ';
            }

            $values = $values . '(';
            $values = $values . '\'' . json_encode($operation) . '\', ';
            $values = $values . '\'\', ';
            $values = $values . '\'wait\'';
            $values = $values . ')';
        }

        $sql = "INSERT INTO " . self::$operations . " (`target_date`, `complete_date`, `type`) VALUES $values";

        return dba::query($sql);
    }

    public static function get_last_operation()
    {
        $sql = "SELECT * FROM " . self::$operations . "  ORDER BY `id` DESC;";
        dba::query($sql);
        $last_operation = dba::fetch_assoc();

        return $last_operation;
    }

    public static function get_first_waited_operation($worker_id)
    {
        $is_lock_success = std_set_lock(self::$lock_name, $worker_id);

        while (!$is_lock_success) {
            time_nanosleep(0, 1000000);
            $is_lock_success = std_set_lock(self::$lock_name, $worker_id);
        }

        if (!std_get_lock_content(self::$lock_name) || std_get_lock_content(self::$lock_name) != $worker_id) {
            return  self::get_first_waited_operation($worker_id);
        }

        $sql = "SELECT * FROM " . self::$operations . " WHERE `type` = 'wait' and `worker_id` IS NULL ORDER BY `id` ASC LIMIT 100;";
        dba::query($sql);
        $new_operations = dba::fetch_assoc_all();

        if (!$new_operations) {
            std_remove_lock(self::$lock_name);
            return false;
        }

        self::set_in_process($new_operations, $worker_id);

        std_remove_lock(self::$lock_name);
        return $new_operations;
    }

    private static function set_in_process($operations, $worker_id)
    {
        $ids = '';

        foreach ($operations as $operation) {
            if ($ids != '') {
                $ids = $ids . ', ';
            }

            $ids = $ids . $operation['id'];
        }

        $sql = "UPDATE " . self::$operations . " SET `type` = 'in_process', `worker_id` = '$worker_id' WHERE `id` in ($ids) and `type` = 'wait'  and `worker_id` IS NULL;";
        $res = dba::query($sql);
        return $res;
    }

    public static function done_operation($id, $worker_id)
    {
        $sql = "UPDATE " . self::$operations . " SET `type` = 'done', `complete_date` = NOW() WHERE `id` = '$id' and `type` = 'in_process'  and `worker_id` = '$worker_id';";
        $res = dba::query($sql);
        return $res;
    }
}
