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

use cfg;

final class telegram
{

    public static function send($data)
    {
        $token = cfg::$telegram_bot_token;
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
        $chat_id = $data['to'];
        $title = $data['title'];
        $text = $data['text'];

        $ch = curl_init($url);
        # Setup request to send json via POST.
        $payload = json_encode(array("chat_id" => $chat_id, "text" => $title . " \n " . $text));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ? true : false;
    }

}
