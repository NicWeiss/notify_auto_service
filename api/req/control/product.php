<?php
/**
 * class product
 * controller for product operation
 */

namespace control;
use DateTime;

use lib\request;
use model\base as base;

class product
{
    public static function get_products()
    {
        $filter = request::get_from_client_Json('filter');
        return base::get_products($filter);
    }

    public static function add_product()
    {
        return base::add_product();
    }

    public static function remove_product()
    {
        $id = request::get_from_client_Json('id');
        return base::remove_product($id);
    }

    public static function edit_product()
    {
        $product = request::get_from_client_Json('product');
        $id = $product['id'];
        $key = $product['key'];
        $value = $product['value'];
        $error = "none";
        $validate = true;

        if($key == "name"){
            if(ctype_space($value)){
                $error = "Имя не может быть только из пробелов" ;
                $validate = false;
            }
        }

        if ($key == "amount" || $key == "cost")
            if (!preg_match('/^\d+$/', $value)) {
                $error = ($key == "amount") ? "Неверное колличество" : "Неверная стоимость";
                $validate = false;
            }

        if ($key == "datetime"){
            if(!self::validateDateTime($value, 'd.m.Y H:i')) {
                $error = "Некорректные дата или время. Ожидается ДД.ММ.ГГГГ ЧЧ:ММ";
                $validate = false;
            }
        }

        if(!$validate){
            return ["status" => "error", "message" => $error];
        }

        if(!base::edit_product($id, $key, $value))return ["status" => "error", "message" => "Неудалось применить изменения"];

        return ["status" => "success"];
    }

    private static function validateDateTime($dateStr, $format)
    {
        $date = DateTime::createFromFormat($format, $dateStr);
        return $date && ($date->format($format) === $dateStr);
    }
}