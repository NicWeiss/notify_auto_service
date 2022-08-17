<?php

namespace lib;


final class Telegram
{

    public static function send($data)
    {
        $config = $GLOBALS['config'];
        $token = $config::$telegram_bot_token;
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
        $chat_id = $data['to'];
        $title = $data['title'];
        $text = $data['text'];

        $curl = curl_init($url);

        # Setup Request to send json via POST.
        $payload = json_encode(array("chat_id" => $chat_id, "text" => $title . " \n " . $text));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);

        # Send Request.
        $result = curl_exec($curl);
        curl_close($curl);

        return $result ? true : false;
    }
}
