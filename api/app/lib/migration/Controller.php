<?php

namespace lib\migration;

use lib\migration\Template as MigrationTemplate;
use lib\migration\Processor as MigrationProcessor;
use model\MigrationModel;

class Controller
{

    public static function create()
    {
        $date = new \DateTime();
        $id = $date->format("Ymd_His_u");
        $class = MigrationTemplate::get_empty_migration($id);
        $filename = MIGRATION_PATH . "migration_" . $id . ".php";
        file_put_contents($filename, $class);
        echo "Migration created, file: '$filename'\n";
    }

    public static function status()
    {
        $mig = MigrationProcessor::get_available_migrations();
        if ($mig) {
            echo "Available migrations:\n";
            $i = 1;
            foreach ($mig as $m) {
                echo "    $i : $m\n";
                $i++;
            }
        }

        $not_exist = MigrationProcessor::get_not_exist_migrations();
        if ($not_exist) {
            echo "!!Not existent migrations ( run 'migration resolve' to get rid of it ):\n";
            $i = 1;
            foreach ($not_exist as $m) {
                echo "    $i : $m\n";
                $i++;
            }
        }
    }

    public static function up($silence = false)
    {
        $migrations = MigrationProcessor::get_available_migrations();
        if (!$migrations)
            return;

        $i = 0;
        foreach ($migrations as $m) {
            $i++;
            if (!$silence) {
                echo "Applying migration '$m'... ";
            }
            if (!MigrationProcessor::apply_migration($m)) {
                echo "\n\tUnable to apply migration '$m'\n";
                return;
            }
            if (!$silence) {
                echo "| ok\n";
            }
        }
    }

    public static function down($migrations_count = 1)
    {
        if ($migrations_count < 0)
            $migrations_count = 1;

        $migrations = MigrationModel::get_last_applied($migrations_count);
        foreach ($migrations as $m) {
            echo "Rolling back migration '$m' ... ";
            if (!MigrationProcessor::rollback_migration($m)) {
                echo "\n\tUnable to rollback migration '$m'\n";
                return;
            }
            echo "| ok \n";
        }
    }

    public static function init()
    {
        if (MigrationModel::init()) {
            return true;
        } else {
            echo "Инициализация не удалась \n";
            return;
        }
    }

    public static function resolve()
    {
        $not_exist = MigrationProcessor::get_not_exist_migrations();
        foreach ($not_exist as $migration) {
            $file = MIGRATION_PATH . $migration . '.php';
            $out = [];
            $code = -1;
            exec('hg log --template "{rev}," "' . $file . '"', $out, $code);
            $rev = implode("\n", $out);
            if ($code != 0) {
                echo "can't find file '" . $file . "' in repository!\n";
                return;
            }
            $rev = explode(",", $rev)[0];

            unset($out);
            exec('hg cat -r ' . $rev . ' "' . $file . '"', $out, $code);
            $content = implode("\n", $out);
            if ($code != 0) {
                echo "" . $file . ": !ERROR! :\n" . $content . "\n";
                return;
            }

            file_put_contents($file, $content);

            echo "Rolling back migration '$migration' ... ";

            //__ changing error handlers __
            set_error_handler('std_mig_error_handler', E_ALL | E_STRICT);
            set_exception_handler('std_mig_exception_handler');
            register_shutdown_function('std_mig_fatal_handler');
            $file_to_delete = $file;
            if (!MigrationProcessor::rollback_migration($migration)) {
                echo "\n\tUnable to rollback migration '$migration'\n";
                return;
            }


            unlink($file);
            $file_to_delete = "";
            echo "| ok\n";
        }
    }
}
