<?php

namespace model;

use lib\DB;

final class WatcherModel
{
    public static $operations = TABLE_OF_OPERATIONS;

    public static function add_operation($target_date)
    {
        $sql = "INSERT INTO " . self::$operations . " (`target_date`, `complete_date`, `type`)
      VALUES('" . json_encode($target_date) . "', '', 'wait')";

        return DB::query($sql);
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

        return DB::query($sql);
    }

    public static function get_last_operation()
    {
        $sql = "SELECT * FROM " . self::$operations . "  ORDER BY `id` DESC;";
        DB::query($sql);
        $last_operation = DB::fetch_assoc();

        return $last_operation;
    }

    public static function get_first_waited_operation($worker_id)
    {
        $sql = "SELECT * FROM " . self::$operations . " WHERE `type` = 'wait' and `worker_id` IS NULL ORDER BY `id` ASC LIMIT 100 FOR UPDATE;";
        $new_operations = DB::fetch_assoc_all($sql);

        if (!$new_operations) {
            return false;
        }

        self::set_in_process($new_operations, $worker_id);

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
        $res = DB::query($sql);
        return $res;
    }

    public static function done_operation($id, $worker_id)
    {
        $sql = "UPDATE " . self::$operations . " SET `type` = 'done', `complete_date` = NOW() WHERE `id` = '$id' and `type` = 'in_process'  and `worker_id` = '$worker_id';";
        $res = DB::query($sql);
        return $res;
    }


    public static function delete_all_done_operations_without_last()
    {
        $operations_table = self::$operations;
        $last_operation = self::get_last_operation();
        $last_id = 0;

        if ($last_operation) {
            $last_id = $last_operation['id'];
        }

        $sql = "DELETE FROM $operations_table WHERE `type` = 'done' AND `id` != $last_id";
        DB::query($sql);
    }
}
