<?php
/**
 * class list
 * controller for list and posts
 */

namespace control;

use lib\request;
use model\base as base;

class posts_list
{
    public static function get_list(){
        $data = [
            'user_id' => 2780,
            'page' => 1,
            'type' => '0',
            'view' => '1',
            'trash' => '0',
            'filter' => ''
        ];
        $list = base::get_news($data);
        $array = [
            "list" => $list
        ];
        return $array;
    }
}