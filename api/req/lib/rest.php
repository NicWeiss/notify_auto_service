<?php

/**
 * Библиотека отвечает за разбор запросов от Фронта по соглашению Rest API
 */

namespace lib;

final class Rest
{
    public static function process($class, $method, $entity_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($entity_id) {
                $class::$method($entity_id);
            } else {
                $class::$method();
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($entity_id) {
                $class::get_by_id($entity_id);
            } else {
                $class::get();
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $class::update($entity_id);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $class::delete($entity_id);
        }
    }
}
