<?php

namespace lib;

require_once('app/lib/PHPRedis/autoloader.php');

use RedisClient\RedisClient;

final class Redis
{
    private static $RedisClient;

    function __construct()
    {
        $config = $GLOBALS['config'];

        self::$RedisClient = new RedisClient([
            'server' => $config::$redis_url,
            'timeout' => 10,
            'password' => $config::$redis_pass,
        ]);
    }


    public static function set($key, $value)
    {
        self::$RedisClient->set($key, $value);
    }

    public static function get($key)
    {
        return self::$RedisClient->get($key);
    }
}
