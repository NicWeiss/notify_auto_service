<?php

/**
 * class auth
 * отвечает за авторизацию и регистрацию пользователей
 */

namespace control;

use generic\component;
use lib\email;
use lib\request;
use model\auth_base as auth_base;

class auth extends component
{
    public static function login()
    {
        /*
         * Метод авторизации пользователя
         *      1.  Будет проверен проверен логин и пароль. если тользователь не найден - 403
         *      2.  Если пользователь найден, то будет обновлена сессия и отправлена на фронт
         */
        $email = request::get_from_client_Json('email');
        $password = md5(request::get_from_client_Json('password'));

        $user = auth_base::get_user($email, $password);
        if (!$user) {
            self::has_no_permission();
            return;
        }

        $session = md5(uniqid(rand(), true));
        $date = date_create();
        $data = [
            'user_id' => $user['id'],
            'session' => $session,
            'expire_at' => (date_timestamp_get($date) + 3600)
        ];
        auth_base::set_session($data);

        self::set_data(['user' => $user['user'], 'session' => $session]);
    }

    public static function get_code()
    {
        /*
         * Метод отправки кода подтверждения
         *      1.  Будет проверен код и если он выслан и активен, то выход
         *      2.  Если подходящего кода нет, то будет создан новый, сохранён в базу и выслан
         */
        $email = request::get_from_client_Json('email');
        $code = random_int(1000, 9999);
        $date = date_create();

        if (auth_base::check_user_exists($email)) {
            self::unprocessable_entity();
            return;
        }
        if (auth_base::is_reg_code_exist($email, date_timestamp_get($date))) {
            self::has_no_permission();
            return;
        }

        auth_base::create_code(['email' => $email, 'code' => $code, 'expire_at' => (date_timestamp_get($date) + 300)]);

        $is_send = email::send([
            'to' => $email,
            'title' => 'Код подтверждения',
            'text' => 'Ваш код подтверждения: ' . $code
                . '. Наберите его в поле ввода длязавершения регистрации'
        ]);
        if (!$is_send) {
            self::critical_error();
            return;
        }
        self::set_data('correct');
    }

    public static function sign_up()
    {
        $date = date_create();

        $accepted_code = request::get_from_client_Json('code');
        $email = request::get_from_client_Json('email');

        $sended_code = auth_base::get_reg_code($email, date_timestamp_get($date));
        if (!$sended_code) {
            self::has_no_permission();
        }

        if ($accepted_code == $sended_code) {
            $session = md5(uniqid(rand(), true));
            $password = md5(request::get_from_client_Json('password'));

            if (auth_base::check_user_exists($email)) {
                self::unprocessable_entity();
                return;
            }

            $user = [
                'user' => request::get_from_client_Json('user'),
                'password' => $password,
                'email' => $email
            ];
            $user_id = auth_base::create_user($user);
            if (!$user_id) {
                self::critical_error();
                return;
            }
            $data = [
                'user_id' => $user_id,
                'session' => $session,
                'expire_at' => (date_timestamp_get($date) + 3600)
            ];
            auth_base::set_session($data);
            self::set_data($session);
            return;
        }
        self::has_no_permission();
    }

    public static function restore()
    {
        $date = date_create();

        $email = request::get_from_client_Json('email');
        if (!$email) {
            self::unprocessable_entity();
            return;
        }

        $restore_hash = md5(strval(random_int(1000, 9999) * random_int(1000, 9999)));


        if (!auth_base::check_user_exists($email)) {
            self::unprocessable_entity();
            return;
        }
        if (auth_base::is_restore_code_exist($email, date_timestamp_get($date))) {
            self::has_no_permission();
            return;
        }

        auth_base::create_restore_code([
            'email' => $email,
            'code' => $restore_hash,
            'expire_at' => (date_timestamp_get($date) + 300)
        ]);

        $is_send = email::send([
            'to' => $email,
            'title' => 'Восстановление пароля',
            'text' => 'Вашa ссылка для восстановления: <br><br>' .
                'https://' . $_SERVER['SERVER_NAME'] . '/auth/restore/' . $restore_hash . '/password'
                . ' <br><br> Если вы не запрашивали восстановление - то проигнорируйте это письмо.'
        ]);
        if (!$is_send) {
            self::critical_error();
            return;
        }
        self::set_data('success');
    }

    public static function verify_restore_code()
    {
        $date = date_create();
        $code = request::get_from_client_Json('code');

        if (!auth_base::is_restore_code_exist_by_code($code, date_timestamp_get($date))) {
            self::unprocessable_entity();
            return;
        }
        self::set_data('exist');
    }

    public static function change_password()
    {
        $date = date_create();
        $code = request::get_from_client_Json('code');
        $password = md5(request::get_from_client_Json('password'));

        $email = auth_base::get_email_by_restore_code($code, date_timestamp_get($date));

        if (!$email) {
            self::unprocessable_entity();
            return;
        }

        auth_base::update_password($email, $password);
        self::set_data('success');
    }
}
