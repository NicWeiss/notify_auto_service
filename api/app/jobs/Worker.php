<?php

namespace jobs;

use \services\WorkerService;

final class Worker
{
    public static function run()
    {
        WorkerService::run();
    }
}
