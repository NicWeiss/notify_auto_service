<?php

namespace lib;

use Exception;
use helpers\Logger as Logger;

final class DB
{
    private static $link = null;
    private static $qhnd = false;
    public static $last_query = '';

    private static function _execute($query)
    {
        $config = $GLOBALS['config'];
        if (!self::$link) {
            echo 'Connection Failure!';
            return false;
        }

        self::$link->select_db($config::$db_name);

        self::$last_query = $query;
        self::$qhnd = mysqli_query(self::$link, $query);
        if (self::$qhnd === false) {
            $context = array(
                'query' => self::$last_query,
                'errors' => mysqli_error_list(self::$link)
            );
            Logger::error("MYSQL ERROR: \n" .  var_dump($context['errors'][0]));
        }
        return (self::$qhnd !== false);
    }


    public static function query($query)
    {
        return self::_execute($query);
    }


    public static function fetch_assoc($query = null)
    {
        if ($query) {
            if (!self::_execute($query)) {
                return false;
            }
        }

        $res = false;
        $qhnd = self::$qhnd;
        if (($qhnd !== false) && ($qhnd !== true)) {
            $res = mysqli_fetch_assoc($qhnd);
        }

        return $res;
    }


    public static function fetch_assoc_all($query = null)
    {
        if ($query) {
            if (!self::_execute($query)) {
                return false;
            }
        }

        $res = array();
        while ($row = self::fetch_assoc()) {
            $res[] = $row;
        }

        return $res;
    }

    private static function establish_connescion($without_table = false)
    {
        $config = $GLOBALS['config'];
        $table = $without_table ? null : $config::$db_name;

        if (self::$link) {
            mysqli_close(self::$link);
        }

        try {
            self::$link = mysqli_connect(
                $config::$db_host,
                $config::$db_user,
                $config::$db_pass,
                $table
            );
        } catch (Exception $e) {
            Logger::error('Error while connection: ' .  $e->getMessage());
            return false;
        }
        return true;
    }

    private static function create_table()
    {
        $config = $GLOBALS['config'];
        $result = mysqli_query(self::$link, "CREATE DATABASE IF NOT EXISTS " . $config::$db_name);

        if (!$result) {
            Logger::error('Can\'t create db ' . $config::$db_name);
            return false;
        }

        return true;
    }


    public static function init()
    {
        $config = $GLOBALS['config'];

        if (!self::establish_connescion()) {
            if (self::establish_connescion(true)) {
                if (self::create_table()) {
                    if (!self::establish_connescion()) {
                        Logger::error('Can\'t to db ' . $config::$db_name);
                        die;
                    }
                } else {
                    Logger::error('Can\'t create db ' . $config::$db_name);
                    die;
                }
            } else {
                Logger::error('Can\'t connect to mysql on ' . $config::$db_host);
                die;
            }
        }

        self::$link->set_charset("utf8");
    }
}
