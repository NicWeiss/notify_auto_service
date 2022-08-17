<?php

namespace lib;


final class Config
{
    public static $db_host = null;
    public static $db_name = null;
    public static $db_user = null;
    public static $db_pass = null;

    public static $redis_url = null;
    public static $redis_pass = null;

    public static $ip_location_provider_token = null;

    public static $email_sender = null;
    public static $email_password = null;

    public static $telegram_bot_token = null;

    function __construct()
    {
        self::$db_host = getenv('DB_HOST');
        self::$db_name = getenv('DB_NAME');
        self::$db_user = getenv('DB_USER');
        self::$db_pass = getenv('DB_PASS');
        self::$ip_location_provider_token = getenv('IP_LOCATION_PROVIDER_TOKEN');
        self::$email_sender = getenv('EMAIL_SENDER');
        self::$email_password = getenv('EMAIL_PASSWORD');
        self::$telegram_bot_token = getenv('TELEGRAM_BOT_TOKEN');
        self::$redis_url = getenv('REDIS_URL');
        self::$redis_pass = getenv('REDIS_PASS');
    }
}
