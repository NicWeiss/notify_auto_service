<?php

namespace jobs;

use \services\WatcherService;

class Watcher
{
    public static function run()
    {
        WatcherService::run();
    }
}
