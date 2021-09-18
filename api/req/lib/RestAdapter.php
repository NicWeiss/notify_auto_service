<?php

/**
 * Библиотека отвечает за разбор запросов от Фронта по соглашению Rest API
 */

namespace lib;

final class RestAdapter
{
    public static function excute($class, $method, $ember_model,  $entity_id = null)
    {
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($entity_id) {
                $result = $class::$method($entity_id);
            } else {
                $result = $class::$method();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($entity_id) {
                $result = $class::get_by_id($entity_id);
            } else {
                $result = $class::get();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $result = $class::update($entity_id);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $result = $class::delete($entity_id);
        }

        http_response_code(200);

        return self::build_result($result, $class, $ember_model);
    }

    private static function build_result($result, $class, $ember_model)
    {
        if ($ember_model) {
            return [
                $ember_model => $result,
                'meta' => [
                    'total_pages' => $class::get_total_pages()
                ]
            ];
        }

        return $result;
    }
}
