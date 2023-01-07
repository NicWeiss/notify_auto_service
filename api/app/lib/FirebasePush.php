<?php

namespace lib;


final class FirebasePush
{

    public static function send($data)
    {
        $config = $GLOBALS['config'];
        $fcm_server_key = $config::$fcm_server_key;
        $url = "https://fcm.googleapis.com/fcm/send";
        $fcm_token = $data['to'];
        $title = $data['title'];
        $text = $data['text'];

        $curl = curl_init($url);

        # Setup Request to send json via POST.
        $payload = json_encode(array("to" => $fcm_token, "notification" => ["title" => $title, "body" => $text]));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:application/json", "Authorization: key=$fcm_server_key"));
        # Return response instead of printing.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        # Send Request.
        $result = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($result, true);

        return $response['failure'] == 0 ? true : false;
    }
}
