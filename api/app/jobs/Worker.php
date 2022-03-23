<?php

namespace jobs;

use \services\worker as WorkerService;

final class Worker
{
    public static function run()
    {
        WorkerService::run();
    }
}
