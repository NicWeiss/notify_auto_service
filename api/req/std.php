<?php

/**
 * lib provided additional functional
 */

use lib\dba as dba;

require_once('req/defines.php');

function std_autoload($classname)
{
    $classname = str_replace('\\', '/', $classname);
    $path = __DIR__ . '/';
    $include = $path . $classname . '.php';
    if (file_exists($include))
        require_once($include);
}

function std_debug($var, $die = true)
{
    var_dump($var);
    if ($die)
        die;
}

function std_log($var)
{
    // $filename = './tmp/log/log.txt';
    // if (!file_exists($filename)) {
    //     mkdir("./tmp/", 0777, true);
    //     mkdir("./tmp/log/", 0777, true);
    //     $fp = fopen($filename, "w");
    //     fwrite($fp, 'INIT LOG' . PHP_EOL);
    //     fclose($fp);
    // }
    // $for_log = print_r(date('m/d/Y H:i:s', time()) . ':      ' . $var, true);
    // file_put_contents($filename, $for_log . PHP_EOL, FILE_APPEND);
}

function std_error_log($var)
{
    // $filename = './tmp/log/error_log.txt';
    // if (!file_exists($filename)) {
    //     mkdir("./tmp/", 0777, true);
    //     mkdir("./tmp/log/", 0777, true);
    //     $fp = fopen($filename, "w");
    //     fwrite($fp, 'INIT ERROR LOG' . PHP_EOL);
    //     fclose($fp);
    // }
    // $for_log = print_r(date('m/d/Y H:i:s', time()) . ':      ' . $var, true);
    // file_put_contents($filename, $for_log . PHP_EOL, FILE_APPEND);
}

function std_env_init($with_error_handlers = true)
{
    spl_autoload_register('std_autoload');
    dba::init();
}
