<?php

namespace jobs;

use \services\watcher as WatcherService;

class Watcher
{
    public static function run()
    {
        WatcherService::run();
    }
}
