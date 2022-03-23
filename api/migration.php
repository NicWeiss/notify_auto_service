<?php

/**
 *  DB migrations system
 *
 * @author Tomilin Dmitriy <bankastudio@gmail.com>
 */

if (php_sapi_name() != 'cli')
    die;

if (exec('whoami') == 'root') {
    echo "No root user allowed!\n";
    exit(-1);
}

define('TIMEZONE', 'Europe/Moscow');
define('PERM_DIR', 0775);
define('PERM_FILE', 0644);

define('TMP_PATH', 'tmp/');
define('MIGRATION_PATH', 'app/migration/');
define('APP_PATH', 'app/');

require_once(TMP_PATH . 'config.ini.php');

require_once(APP_PATH . 'std.php');
std_env_init();

use lib\migration;
use model\migration as mmigration;

function print_help()
{
    die("usage:
    php migration.php  {command} [params]
commands:
    init                            - Инициализация таблицы миграций
    create                          - Создать миграцию;
    up [count]                      - Применить миграции (применит все доступные миграции);
    down [count|migration_id]       - Откатить миграции (default count=1);
    stat                            - Показать доступные миграции без применения.
    resolve                         - Попытаться найти и откатить миграцию которая была удалена с диска, но есть базе.
");
}

if ($argc < 2)
    print_help();

$file_to_delete = "";

for ($ci = 1; $ci < $argc; $ci++) {
    $command = $argv[$ci];
    switch ($command) {
        case 'create':
            $date = new \DateTime();
            $id = $date->format("Ymd_His_u");
            $class = <<<CLASS
<?php
/*
*   migration $id
*/

namespace migration;
use generic\migration;
use lib\dba;


final class migration_$id extends migration
{

    protected \$comment = 'No comment';

    protected function up(){
        \$query ="";
        if(!dba::query(\$query))
            return false;

        return true;
    }

    protected function down(){
        \$query ="";
        if(!dba::query(\$query))
            return false;

        return true;
    }
}

CLASS;
            $filename = MIGRATION_PATH . "migration_" . $id . ".php";
            file_put_contents($filename, $class);
            echo "Migration created, file: '$filename'\n";

            break;

        case 'stat':
            $mig = migration::get_available_migrations();
            if ($mig) {
                echo "Available migrations:\n";
                $i = 1;
                foreach ($mig as $m) {
                    echo "    $i : $m\n";
                    $i++;
                }
            }

            $not_exist = migration::get_not_exist_migrations();
            if ($not_exist) {
                echo "!!Not existent migrations ( run 'migration resolve' to get rid of it ):\n";
                $i = 1;
                foreach ($not_exist as $m) {
                    echo "    $i : $m\n";
                    $i++;
                }
            }


            break;

        case 'up':
            $count = -1;
            if (isset($argv[$ci + 1])) {
                $count = (int)$argv[$ci + 1];
                $ci++;
            }

            $migrations = migration::get_available_migrations();
            if (!$migrations)
                break;
            $i = 0;
            foreach ($migrations as $m) {
                $i++;
                if (($count > 0) && ($i > $count))
                    break;
                echo "Applying migration '$m'... ";
                if (!migration::apply_migration($m)) {
                    echo "\n\tUnable to apply migration '$m'\n";
                    break;
                }
                echo "| ok\n";
            }

            break;

        case 'down':
            $count = -1;
            if (isset($argv[$ci + 1])) {
                if (is_numeric($argv[$ci + 1])) {
                    $count = (int)$argv[$ci + 1];
                    $ci++;
                } else {
                    $migration_id = $argv[$ci + 1];
                    $ci++;
                    echo "Rolling back migration '$migration_id' ... ";
                    if (!migration::rollback_migration($migration_id)) {
                        echo "\n\tUnable to rollback migration '$migration_id'\n";
                        break;
                    }
                    die("| ok \n");
                }
            }

            if ($count < 0)
                $count = 1;
            $migrations = mmigration::get_last_applied($count);
            foreach ($migrations as $m) {
                echo "Rolling back migration '$m' ... ";
                if (!migration::rollback_migration($m)) {
                    echo "\n\tUnable to rollback migration '$m'\n";
                    break;
                }
                echo "| ok \n";
            }
            break;

        case 'resolve':
            $not_exist = migration::get_not_exist_migrations();
            foreach ($not_exist as $migration) {
                $file = MIGRATION_PATH . $migration . '.php';
                $out = [];
                $code = -1;
                exec('hg log --template "{rev}," "' . $file . '"', $out, $code);
                $rev = implode("\n", $out);
                if ($code != 0) {
                    echo "can't find file '" . $file . "' in repository!\n";
                    break;
                }
                $rev = explode(",", $rev)[0];

                unset($out);
                exec('hg cat -r ' . $rev . ' "' . $file . '"', $out, $code);
                $content = implode("\n", $out);
                if ($code != 0) {
                    echo "" . $file . ": !ERROR! :\n" . $content . "\n";
                    break;
                }

                file_put_contents($file, $content);

                echo "Rolling back migration '$migration' ... ";

                //__ changing error handlers __
                set_error_handler('std_mig_error_handler', E_ALL | E_STRICT);
                set_exception_handler('std_mig_exception_handler');
                register_shutdown_function('std_mig_fatal_handler');
                $file_to_delete = $file;
                if (!migration::rollback_migration($migration)) {
                    echo "\n\tUnable to rollback migration '$migration'\n";
                    break;
                }


                unlink($file);
                $file_to_delete = "";
                echo "| ok\n";
            }
            break;
            break;
        case 'init':
            if (mmigration::init()) {
                echo "Инициализация прошла успешно \n";
                break;
            } else {
                echo "Инициализация не удалась";
            }
            break;

        default:
            print_help();
            break;
    }
}

/**
 * PHP error handler
 * @param int $type
 * @param string $message
 * @param string $file
 * @param int $line
 */
function std_mig_error_handler($type, $message, $file, $line)
{
    global $file_to_delete;
    //skip all errors during @function calls
    if (error_reporting() == 0)
        return;

    $context = array(
        'type' => std_get_err_name($type),
        'file' => $file,
        'line' => $line
    );

    if ($file_to_delete)
        unlink($file_to_delete);
}

/**
 * PHP exception handler
 * @param exception $ex
 * @return string
 */
function std_mig_exception_handler($ex)
{
    global $file_to_delete;
    $context = array(
        'type' => $ex->getCode(),
        'file' => $ex->getFile(),
        'line' => $ex->getLine()
    );

    if ($file_to_delete)
        unlink($file_to_delete);
}

/**
 * PHP fatal error handler
 */
function std_mig_fatal_handler()
{
    global $file_to_delete;
    $context = error_get_last();
    if (!$context)
        return;

    //skip non-fatal errors
    $fatal = array(E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR);
    if (array_search($context['type'], $fatal) === false)
        return;
    $context['type'] = std_get_err_name($context['type']);
    $message = $context['message'];
    unset($context['message']);

    if ($file_to_delete)
        unlink($file_to_delete);
}
