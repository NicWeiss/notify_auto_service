<?php
header("Access-Control-Allow-Origin: *");

use helpers\Logger as Logger;
use lib\request as request;


require_once("req/std.php");
require_once('tmp/config.ini.php');
std_env_init();


final class cron_job
{
    public static function run()
    {
        $job_name = $_SERVER['argv'][1];

        if (!$job_name) {
            Logger::error('Job name is empty!');
            die;
        }

        $job = 'jobs\\' . $job_name;
        $job::run();
    }
}

cron_job::run();
