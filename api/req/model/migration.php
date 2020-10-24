<?php
/**
 *
 *
 * @author Tomilin Dmitriy <bankastudio@gmail.com>
 * @copyright Copyright (c) 2018, Tomilin Dmitriy
 * @package
 */

namespace model;

use lib\dba as dba;

class migration
{

    /**
     * @return array|bool
     */
    public static function get_list(){
        $tb = MIGRATION;

        $query = "SELECT id FROM {$tb}";
        if(!dba::query($query))
            return false;

        $result = [];
        foreach (dba::fetch_assoc_all() as $row){
            $result[]=$row['id'];
        }
        return $result;
    }

    public static function get_last_applied($count){
        $tb = MIGRATION;
        $count = (int)$count;
        $query = "SELECT id FROM $tb ORDER BY apply_date DESC LIMIT {$count}";
        if(!dba::query($query))
            return false;

        $result = [];
        foreach (dba::fetch_assoc_all() as $row){
            $result[]=$row['id'];
        }

        return $result;
    }

    /*
     * register migration in migrations table
     */
    public static function register_migration($migration_id, $comment){
        $DB = MIGRATION;
        $sql = "INSERT INTO  $DB ( `id`, `comment`, `apply_date`) 
                VALUES ('$migration_id',  '$comment', NOW() )";
        dba:: query($sql);
    }

    /*
     * unregister migration from migrations table
     */
    public static function unregister_migration($migration_id){
        $tb = MIGRATION;

        $query = "DELETE FROM {$tb} WHERE id = ?";
        return dba::query_wild($query, $migration_id);
    }

}
