<?php

/**
 * @author Tomilin Dmitriy <bankastudio@gmail.com>
 * @package
 */

namespace model;

use lib\DB;

class MigrationModel
{

    public static function init()
    {
        $query = "CREATE TABLE `migration` (`id` VARCHAR(45) NULL,
            `comment` VARCHAR(45) NULL, `apply_date` DATETIME);";
        return DB::query($query);
    }

    /**
     * @return array|bool
     */
    public static function get_list()
    {
        $tb = MIGRATION;

        $query = "SELECT id FROM {$tb}";
        if (!DB::query($query))
            return false;

        $result = [];
        foreach (DB::fetch_assoc_all() as $row) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public static function get_last_applied($count)
    {
        $tb = MIGRATION;
        $count = (int)$count;
        $query = "SELECT id FROM $tb ORDER BY apply_date DESC LIMIT {$count}";
        if (!DB::query($query))
            return false;

        $result = [];
        foreach (DB::fetch_assoc_all() as $row) {
            $result[] = $row['id'];
        }

        return $result;
    }

    /*
     * register migration in migrations table
     */
    public static function register_migration($migration_id, $comment)
    {
        $DB = MIGRATION;
        $sql = "INSERT INTO  $DB ( `id`, `comment`, `apply_date`)
                VALUES ('$migration_id',  '$comment', NOW() )";
        DB::query($sql);
    }

    /*
     * unregister migration from migrations table
     */
    public static function unregister_migration($migration_id)
    {
        $tb = MIGRATION;

        $sql = "DELETE FROM {$tb} WHERE id = '" . $migration_id . '"';
        return DB::query($sql);
    }
}
