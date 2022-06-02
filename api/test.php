<?php

// Waiting database start ...
sleep(2);

use lib\request as request;

require_once("app/std.php");
std_env_init();


final class testing
{
    public static function run()
    {
        request::init();
    }
}


testing::run();
