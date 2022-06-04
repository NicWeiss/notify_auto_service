<?php

namespace lib\migration;

use \model\MigrationModel;


class Processor
{
    /**
     * returns not applied migrations list
     * @return array
     */
    public static function get_available_migrations()
    {
        $migrations = scandir('app/migration/');
        $db_migrations = MigrationModel::get_list();

        if (gettype($db_migrations) != 'array') {
            print("Таблица миграций не инициализирована! \n");
            die;
        }

        $result = [];

        foreach ($migrations as $m) {
            if (in_array($m, ['.', '..']))
                continue;

            $mn = substr($m, 0, strrpos($m, '.'));

            if (!in_array($mn, $db_migrations)) {
                $result[] = $mn;
            }
        }

        sort($result);
        return $result;
    }

    /**
     * @param $migration string
     * @return bool
     */
    public static function apply_migration($migration_id)
    {
        $classname = 'migration\\' . $migration_id;
        /**
         * @var $m \generic\migration
         */
        $m = new $classname($migration_id);
        return $m->do_up();
    }

    /**
     * @param $migration string
     * @return bool
     */
    public static function rollback_migration($migration_id)
    {
        $classname = 'migration\\' . $migration_id;
        /**
         * @var $m \generic\migration
         */
        $m = new $classname($migration_id);
        return $m->do_down();
    }


    /**
     * returns all applied migrations
     * @return array|bool
     */
    public static function get_full_applied_list()
    {
        return MigrationModel::get_list();
    }

    /**
     * returns not existent migrations
     * @param bool $as_file_names
     * @return array
     */
    public static function get_not_exist_migrations($as_file_names = false)
    {
        $not_exist = [];
        foreach (MigrationModel::get_list() as $m) {
            $filename = 'app/migration/' . $m . '.php';
            if (!file_exists($filename))
                $not_exist[] = $as_file_names ? $filename : $m;
        }
        arsort($not_exist);
        return $not_exist;
    }
}
