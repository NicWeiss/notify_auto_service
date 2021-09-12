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

function std_set_lock($lock_name, $text = 'LOCK')
{
    if (std_check_lock($lock_name)) {
        return false;
    }

    $filename = './tmp/lock/' . $lock_name . '.txt';
    $fp = fopen($filename, "w");
    fwrite($fp, $text);
    fclose($fp);
    time_nanosleep(0, 10000000);
    return true;
}

function std_check_lock($lock_name)
{
    $filename = './tmp/lock/' . $lock_name . '.txt';
    return !!file_exists($filename);
}

function std_remove_lock($lock_name)
{
    if (!std_check_lock($lock_name)) {
        return false;
    }

    unlink('./tmp/lock/' . $lock_name . '.txt');
    time_nanosleep(0, 10000000);
    return true;
}

function std_get_lock_content($lock_name)
{
    if (!std_check_lock($lock_name)) {
        return false;
    }

    $filename = './tmp/lock/' . $lock_name . '.txt';
    return file_get_contents($filename, true);
}


function std_env_init($with_error_handlers = true)
{
    spl_autoload_register('std_autoload');
    dba::init();
}
