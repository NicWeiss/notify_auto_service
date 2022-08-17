<?php

/**
 * Dispatcher
 * retun data for call
 */

use lib\Request;

final class Dispatcher
{
    private static $route_map = array();


    public static function add($url, $resource)
    {
        self::$route_map[$url] = $resource;
    }


    public static function dispatch()
    {
        $path = Request::$path;
        $resource = [];
        $matches = null;

        foreach (self::$route_map as $url => $t_resource) {
            if (preg_match("~^{$url}$~u", $path, $matches)) {
                $resource = $t_resource;
                break;
            }
        }

        array_shift($matches);

        foreach ($matches as &$val)
            if (preg_match("~^(\d+)$~u", $val))
                $val = intval($val, 10);

        $resource['entity_id'] =  count($matches) > 0 ? $matches[0] : "";

        if (!$resource) $resource = array(
            'control_class' => 'control\Stub',
            'control_function' => 'init'
        );

        return $resource;
    }
}

require_once('app/routes/__init__.php');
