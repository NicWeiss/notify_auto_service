<?php
header("Access-Control-Allow-Origin: *");

use helpers\Logger as Logger;


require_once("app/std.php");
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
