<?php

function autoloader($classname)
{
    $classname = str_replace('\\', '/', $classname);
    $path = __DIR__ . '/';
    $include = $path . $classname . '.php';
    if (file_exists($include))
        require_once($include);
}


function autoload_phpunit()
{
    spl_autoload_register('autoloader');
}
