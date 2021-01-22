<?php

namespace lib;

use cfg as cfg;
use PHPMailer;

final class email
{

    public static function send($data)
    {
        require_once('req/lib/PHPMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer();


        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = cfg::$emailconf['email'];
        $mail->Password = cfg::$emailconf['password'];

        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setLanguage('en');

        $mail->setFrom(cfg::$emailconf['email'], 'Notifier System');
        $mail->addAddress($data['to']);
        $mail->isHTML(true);

        $mail->Subject = $data['title'];
        $mail->Body = $data['text'];
        //        $mail->AltBody = 'Оповещение';

        //Отправка сообщения
        if (!$mail->send()) {
            return false;
        }
        return true;
    }
}
