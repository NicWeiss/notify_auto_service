<?php

namespace lib;

use PHPMailer;

final class Email
{

    public static function send($data)
    {
        require_once('app/lib/PHPMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer();
        $config = $GLOBALS['config'];

        $mail->isSMTP();
        $mail->Host = 'smtp.mail.yahoo.com';
        $mail->SMTPAuth = true;
        $mail->Username = $config::$email_sender;
        $mail->Password = $config::$email_password;

        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setLanguage('en');

        $mail->setFrom($config::$email_sender, 'Notifier System');
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
