<?php

namespace helpers;

use DateTime;
use ErrorException;

class Logger
{
    private static $log_level = '';
    private static $log_filename = './tmp/log/log.txt';
    private static $max_file_size = 5000000;

    public static function info($message)
    {
        self::$log_level = 'INFO';

        self::create_log($message);
    }

    public static function warning($message)
    {
        self::$log_level = 'WARNING';

        self::create_log($message);
    }

    public static function error($message)
    {
        self::$log_level = 'ERROR';

        self::create_log($message);
    }

    private static function create_log($message)
    {
        echo $message . "\n";
        $json = self::jsonise_message($message);
        self::check_file();
        self::write_log($json);
    }

    private static function jsonise_message($message)
    {
        $data = array();

        if (gettype($message) == 'array') {
            $data = $message;
        } else if (gettype($message) == 'string') {
            $data['message'] = $message;
        } else {
            throw new ErrorException('Wrong message type. Use String or Array');
        }

        $date = new DateTime();
        $timestamp = $date->getTimestamp();

        $data['timestamp'] = $timestamp;
        $data['loglevel'] = strval(self::$log_level);

        return json_encode($data);
    }

    private static function check_file()
    {
        if (file_exists(self::$log_filename)) {
            if (filesize(self::$log_filename) > self::$max_file_size) {
                self::clear_file();
                self::create_file();
            }
        } else {
            self::create_file();
        }
    }

    private static function create_file()
    {
        mkdir("./tmp/", 0777, true);
        mkdir("./tmp/log/", 0777, true);

        file_put_contents(self::$log_filename, '-----------CLEAR-----------' . PHP_EOL);
    }

    private static function clear_file()
    {
        $file = fopen(self::$log_filename, 'w');
        fwrite($file, 'INIT LOG' . PHP_EOL);
        fclose($file);
    }

    private static function write_log($json)
    {
        file_put_contents(self::$log_filename, $json . PHP_EOL, FILE_APPEND);
    }
}
