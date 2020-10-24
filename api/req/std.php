<?php
/**
 * lib provided additional functional
 */

use lib\dba as dba;

require_once('req/defines.php');

function std_autoload($classname){
    $classname = str_replace('\\', '/', $classname);
    $path = __DIR__.'/';
    $include = $path.$classname.'.php';
    if (file_exists($include))
        require_once($include);
}

function std_debug($var, $die = true){
    var_dump($var);
    if ($die)
        die;
}

function std_env_init($with_error_handlers=true){
    spl_autoload_register('std_autoload');
    dba :: init();
}
