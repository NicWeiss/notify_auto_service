<?php
/**
 * Dispatcher
 * retun data for call
 */

use lib\request as request;

final class dispatcher
{

    private static $route_map = array();

    public static function add($url, $resource){
        self::$route_map[$url] = $resource;
    }


    public static function dispatch(){
        $path = request::$path;
        $resource = "";
        foreach (self::$route_map as $url => $t_resource)
            if (preg_match("~^{$url}$~u", $path, $t_matches)){
                $resource = $t_resource;
                break;
            }
        if (!$resource) $resource =  array(
            'control_class' => 'control\stub',
            'control_function' => 'init'
        );
        return $resource;
    }
}

require_once('req/routes.php');