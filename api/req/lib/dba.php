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

final class dba{
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

    private static function _execute($query){
        self :: $last_query = $query;
        if (self :: $simulate)
            return true;
        self :: $qhnd = mysqli_query(self :: $link, $query);
        if (self :: $qhnd === false){
            $context = array(
                'query' => self :: $last_query,
                'errors' => mysqli_error_list(self :: $link)
            );

        }
        return (self :: $qhnd !== false);
    }
    /**
     * wildcarded query executor
     * each wildcard ? or '?' replaced with corresponding parameter
     * it will be NULL or 'escaped_parameter_value'
     * @param string $query
     * @param string $arg variable list of arguments
     * @return bool success of operation
     */
    public static function query_wild($query){
        $query = str_replace("'?'", '?', $query);//support for both of ? and '?'
        $qarr = explode('?', $query);
        //
        if (func_num_args() != count($qarr))
            trigger_error('Wrong parameter count', E_USER_ERROR);
        //
        $query = array_shift($qarr);
        $f = 1;
        foreach($qarr as $qpart){
            $arg = func_get_arg($f);
            $arg = ($arg === null) ?
                'NULL' : "'".mysqli_real_escape_string(self :: $link, $arg)."'";
            $query .= $arg;
            $query .= $qpart;
            $f++;
        }
        return self :: _execute($query);
    }
    /**
     * full-defined query execution
     * @param string $query
     * @return bool success of operation
     */
    public static function query($query){
        return self :: _execute($query);
    }

    /**
     * query values count
     * @param string $tblname table name
     * @param string $where WHERE condition
     * @return int|array
     */
    public static function query_count($tblname, $where = '1'){
        $res = 0;
        $sql = "SELECT COUNT(*) AS cnt FROM {$tblname} WHERE {$where}";
        if (self :: query($sql)){
            $res = self :: fetch_assoc();
            $res = intval($res['cnt'], 10);
        }
        else
            return false;
        return $res;
    }
    /**
     * returns next row from query result
     * as an associative array
     * @return mixed false if no rows or array with row data
     */
    public static function fetch_assoc(){
        $res = false;
        $qhnd = self :: $qhnd;
        if (($qhnd !== false) && ($qhnd !== true))
            $res = mysqli_fetch_assoc($qhnd);
        return $res;
    }
    /**
     * returns all fetched records in assoc array
     * @param mixed $key_from null or string - group result by this key
     * @return array
     */
    public static function fetch_assoc_all($key_from = null){
        $res = array();
        if ($key_from)
            while ($row = self :: fetch_assoc())
                $res[$row[$key_from]] = $row;
        else
            while ($row = self :: fetch_assoc())
                $res[] = $row;
        return $res;
    }
    /**
     * check existence of table in db
     * @param $tblname string
     * @return bool
     */
    public static function table_exist($tblname){
        $query = "SHOW TABLES LIKE ?";
        self::query_wild($query, $tblname);
        return dba::num_rows()==1;
    }

    public static function init(){

        self::$link = mysqli_connect(cfg:: $siteconf['dbhost'], cfg:: $siteconf['dbuser'],
            cfg :: $siteconf['dbpass'], cfg :: $siteconf['dbname']);
        if (!self :: $link)
            std_debug('cant connect to mysql');
            self :: $link->set_charset("utf8");
    }
}
