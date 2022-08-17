<?php

/**
 * lib provided additional functional
 */

use lib\DB;
use lib\Config;
use lib\Redis;

require_once('app/Defines.php');

static $redis_client;

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

    $redis_client = $GLOBALS['redis'];
    $redis_client->set($lock_name, $text);

    return true;
}

function std_check_lock($lock_name)
{
    $redis_client = $GLOBALS['redis'];

    return !!$redis_client->get($lock_name);
}

function std_remove_lock($lock_name)
{
    if (!std_check_lock($lock_name)) {
        return false;
    }

    $redis_client = $GLOBALS['redis'];
    $redis_client->set($lock_name, '');

    return true;
}

function std_get_lock_content($lock_name)
{
    if (!std_check_lock($lock_name)) {
        return false;
    }

    $redis_client = $GLOBALS['redis'];

    return $redis_client->get($lock_name);
}


function std_env_init($with_error_handlers = true)
{
    spl_autoload_register('std_autoload');

    $config = new Config();
    $GLOBALS['config'] = $config;

    DB::init();
    $GLOBALS['redis'] =  new Redis();
}
