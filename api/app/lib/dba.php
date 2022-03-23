<?php

namespace lib;

use cfg as cfg;
use Exception;
use helpers\Logger as Logger;

final class dba
{
    private static $link = null;
    private static $qhnd = false;
    public static $last_query = '';

    private static function _execute($query)
    {
        self::$last_query = $query;
        self::$qhnd = mysqli_query(self::$link, $query);
        if (self::$qhnd === false) {
            $context = array(
                'query' => self::$last_query,
                'errors' => mysqli_error_list(self::$link)
            );
        }
        return (self::$qhnd !== false);
    }


    public static function query($query)
    {
        return self::_execute($query);
    }


    public static function fetch_assoc()
    {
        $res = false;
        $qhnd = self::$qhnd;
        if (($qhnd !== false) && ($qhnd !== true))
            $res = mysqli_fetch_assoc($qhnd);
        return $res;
    }


    public static function fetch_assoc_all($key_from = null)
    {
        $res = array();
        if ($key_from)
            while ($row = self::fetch_assoc())
                $res[$row[$key_from]] = $row;
        else
            while ($row = self::fetch_assoc())
                $res[] = $row;
        return $res;
    }


    public static function init()
    {

        try {
            self::$link = mysqli_connect(
                cfg::$siteconf['dbhost'],
                cfg::$siteconf['dbuser'],
                cfg::$siteconf['dbpass'],
                cfg::$siteconf['dbname']
            );
        } catch (Exception $e) {
            echo 'Поймано исключение: ',  $e->getMessage(), "\n";
        }

        if (!self::$link) {
            $link = mysqli_connect(
                cfg::$siteconf['dbhost'],
                cfg::$siteconf['dbuser'],
                cfg::$siteconf['dbpass']
            );
            if (!$link) {
                Logger::error('cant connect to mysql');
                die;
            }
            $link->set_charset("utf8");
            $result = mysqli_query($link, "CREATE DATABASE IF NOT EXISTS " . cfg::$siteconf['dbname']);

            if (!$result) {
                echo "Не получилось создать базу " . cfg::$siteconf['dbname'];
                die;
            }

            mysqli_close($link);
            self::init();
        }
        self::$link->set_charset("utf8");
    }
}
