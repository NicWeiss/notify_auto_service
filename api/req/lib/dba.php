<?php

/**
 * MySQLi access library / cutted by Nic Weiss [2020]
 *
 * @author Lemeshev Sergey <daemon.user@gmail.com>
 * @copyright Copyright (c) 2012, Lemeshev Sergey
 *
 * @package lib
 */

namespace lib;

use cfg as cfg;
use Exception;

final class dba
{
    /**
     * mysqli connection
     * @var \mysqli
     */
    private static $link = null;
    /**
     * mysqli executed query id
     * @var resource
     */
    private static $qhnd = false;
    /**
     * set this to true to prevent queries from being executed
     * @var bool
     */
    public static $simulate = false;
    /**
     * last formatted query
     * @var string
     */
    public static $last_query = '';

    private static function _execute($query)
    {
        self::$last_query = $query;
        if (self::$simulate)
            return true;
        self::$qhnd = mysqli_query(self::$link, $query);
        if (self::$qhnd === false) {
            $context = array(
                'query' => self::$last_query,
                'errors' => mysqli_error_list(self::$link)
            );
        }
        return (self::$qhnd !== false);
    }
    /**
     * wildcarded query executor
     * each wildcard ? or '?' replaced with corresponding parameter
     * it will be NULL or 'escaped_parameter_value'
     * @param string $query
     * @param string $arg variable list of arguments
     * @return bool success of operation
     */
    public static function query_wild($query)
    {
        $query = str_replace("'?'", '?', $query); //support for both of ? and '?'
        $qarr = explode('?', $query);
        //
        if (func_num_args() != count($qarr))
            trigger_error('Wrong parameter count', E_USER_ERROR);
        //
        $query = array_shift($qarr);
        $f = 1;
        foreach ($qarr as $qpart) {
            $arg = func_get_arg($f);
            $arg = ($arg === null) ?
                'NULL' : "'" . mysqli_real_escape_string(self::$link, $arg) . "'";
            $query .= $arg;
            $query .= $qpart;
            $f++;
        }
        return self::_execute($query);
    }
    /**
     * full-defined query execution
     * @param string $query
     * @return bool success of operation
     */
    public static function query($query)
    {
        return self::_execute($query);
    }

    public static function query_insert($tblname, $row, $as_is = array())
    {
        $rows = array($row);
        $query = self::build_insert_query($tblname, $rows, $as_is);
        return self::_execute($query);
    }

    public static function build_insert_query($tblname, $data, $as_is = array())
    {
        $query = "INSERT INTO `" . $tblname . "`";
        //fetch field names
        reset($data);
        $fields = current($data);
        $fields = array_keys($fields);
        //escape field names
        foreach ($fields as &$field)
            $field = "`{$field}`";
        //
        $fields = implode(',', $fields);
        $fields = " ({$fields}) ";
        //
        $value = [];
        foreach ($data as $elem) {
            $_value = array();
            foreach ($elem as $fname => $fvalue) {
                if (array_search($fname, $as_is) !== false)
                    $_value[] = "{$fvalue}";
                else if ($fvalue === null)
                    $_value[] = 'NULL';
                else {
                    $fvalue = str_replace('?', '[q]', $fvalue);
                    $_value[] = "'" . mysqli_real_escape_string(self::$link, $fvalue) . "'";
                }
            }
            $_value = implode(',', $_value);
            $value[] = ' (' . $_value . ') ';
        }
        if ($value)
            $value = ' VALUES ' . implode(', ', $value);
        return str_replace('[q]', '?', $query . $fields . $value);
    }

    public static function num_rows()
    {
        $qhnd = self::$qhnd;
        switch ($qhnd) {
            case true:
                $res = mysqli_affected_rows(self::$link);
                break;
            case false:
                $res = -1;
                break;
            default:
                $res = mysqli_num_rows($qhnd);
        }
        return $res;
    }

    /**
     * query values count
     * @param string $tblname table name
     * @param string $where WHERE condition
     * @return int|array
     */
    public static function query_count($tblname, $where = '1')
    {
        $res = 0;
        $sql = "SELECT COUNT(*) AS cnt FROM {$tblname} WHERE {$where}";
        if (self::query($sql)) {
            $res = self::fetch_assoc();
            $res = intval($res['cnt'], 10);
        } else
            return false;
        return $res;
    }
    /**
     * returns next row from query result
     * as an associative array
     * @return mixed false if no rows or array with row data
     */
    public static function fetch_assoc()
    {
        $res = false;
        $qhnd = self::$qhnd;
        if (($qhnd !== false) && ($qhnd !== true))
            $res = mysqli_fetch_assoc($qhnd);
        return $res;
    }
    /**
     * returns all fetched records in assoc array
     * @param mixed $key_from null or string - group result by this key
     * @return array
     */
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
    /**
     * check existence of table in db
     * @param $tblname string
     * @return bool
     */
    public static function table_exist($tblname)
    {
        $query = "SHOW TABLES LIKE ?";
        self::query_wild($query, $tblname);
        return dba::num_rows() == 1;
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
                std_debug('cant connect to mysql');
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
