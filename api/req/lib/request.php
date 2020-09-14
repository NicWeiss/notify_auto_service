<?php
/**
 * lib provided splited data for dispatcher
 */
namespace lib;

final class request{

    public static $url = array();
    public static $referrer = array();
    public static $path = '';
    public static $req = array();
    private static $ip = null;


    public static function split_given_url($url = null){
        $url = @parse_url($url, PHP_URL_PATH);
        $url = urldecode($url);
        $arr = explode('/', $url);
        $url = array();
        foreach ($arr as $elem)
            if ($elem !== '')
                $url[] = $elem;

        return $url;
    }

    public static function get($name, $default = null, $int = false){
        $res = (array_key_exists($name, self :: $req)) ? self :: $req[$name] : $default;
        if ($int)
            $res = intval($res, 10);
        return $res;
    }

    public static function get_from_client_Json($name){
        $data =  json_decode(file_get_contents('php://input'), true);
        return $data[$name];
    }

    public static function init($url = null){
        if (!$url)
            $url = $_SERVER['REQUEST_URI'];

        $url = self :: split_given_url($url);
        self :: $url = $url;
        self :: $referrer = isset($_SERVER['HTTP_REFERER']) ? self :: split_given_url($_SERVER['HTTP_REFERER']) : [];
        self :: $path = implode('/', $url);
        self :: $req = array_merge($_GET, $_POST);
    }
}