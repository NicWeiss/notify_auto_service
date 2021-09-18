<?php

/**
 * class auth
 * отвечает за авторизацию и регистрацию пользователей
 */

namespace control;

use generic\BaseController;
use lib\email;
use lib\request;
use model\auth_base as auth_base;

class auth extends BaseController
{
    private static $ONE_WEEK = 604800;
    private static $ONE_HUNDRED_YEARS = 338688000;

    public static function login()
    {
        /*
         * Метод авторизации пользователя
         *      1.  Будет проверен проверен логин и пароль. если тользователь не найден - 403
         *      2.  Если пользователь найден, то будет обновлена сессия и отправлена на фронт
         */
        $email = request::get_from_client_Json('email');
        $is_from_mobile = request::get_from_client_Json('is_from_mobile');
        $password = md5(request::get_from_client_Json('password'));

        $user = auth_base::get_user($email, $password);

        if (!$user) {
            throw self::has_no_permission();
        }

        $session_id = md5(uniqid(rand(), true));
        $session_life_time = $is_from_mobile ? self::$ONE_HUNDRED_YEARS : self::$ONE_WEEK;
        $current_date = date_create();

        $session = [
            'user_id' => $user['id'],
            'session' => $session_id,
            'expire_at' => (date_timestamp_get($current_date) + $session_life_time)
        ];

        auth_base::set_session($session);

        return ['user' => $user['user'], 'session' => $session_id];
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
            throw self::unprocessable_entity();
        }
        if (auth_base::is_reg_code_exist($email, date_timestamp_get($date))) {
            throw self::has_no_permission();
        }

        auth_base::create_code(['email' => $email, 'code' => $code, 'expire_at' => (date_timestamp_get($date) + 300)]);

        $is_send = email::send([
            'to' => $email,
            'title' => 'Код подтверждения',
            'text' => 'Ваш код подтверждения: ' . $code
                . '. Наберите его в поле ввода длязавершения регистрации'
        ]);
        if (!$is_send) {
            throw self::critical_error();
        }

        return 'correct';
    }

    public static function sign_up()
    {
        $date = date_create();

        $accepted_code = request::get_from_client_Json('code');
        $email = request::get_from_client_Json('email');

        $sended_code = auth_base::get_reg_code($email, date_timestamp_get($date));
        if (!$sended_code) {
            throw self::has_no_permission();
        }

        if ($accepted_code == $sended_code) {
            $session = md5(uniqid(rand(), true));
            $password = md5(request::get_from_client_Json('password'));

            if (auth_base::check_user_exists($email)) {
                throw self::unprocessable_entity();
            }

            $user = [
                'user' => request::get_from_client_Json('user'),
                'password' => $password,
                'email' => $email
            ];
            $user_id = auth_base::create_user($user);

            if (!$user_id) {
                throw self::critical_error();
            }

            $data = [
                'user_id' => $user_id,
                'session' => $session,
                'expire_at' => (date_timestamp_get($date) + 3600)
            ];

            auth_base::set_session($data);

            return $session;
        }

        throw self::has_no_permission();
    }

    public static function restore()
    {
        $date = date_create();

        $email = request::get_from_client_Json('email');
        if (!$email) {
            throw self::unprocessable_entity();
        }

        $restore_hash = md5(strval(random_int(1000, 9999) * random_int(1000, 9999)));


        if (!auth_base::check_user_exists($email)) {
            throw self::unprocessable_entity();
        }
        if (auth_base::is_restore_code_exist($email, date_timestamp_get($date))) {
            throw self::has_no_permission();
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
            throw self::critical_error();
        }

        return 'success';
    }

    public static function verify_restore_code()
    {
        $date = date_create();
        $code = request::get_from_client_Json('code');

        if (!auth_base::is_restore_code_exist_by_code($code, date_timestamp_get($date))) {
            throw self::unprocessable_entity();
        }

        return 'exist';
    }

    public static function change_password()
    {
        $date = date_create();
        $code = request::get_from_client_Json('code');
        $password = md5(request::get_from_client_Json('password'));

        $email = auth_base::get_email_by_restore_code($code, date_timestamp_get($date));

        if (!$email) {
            throw self::unprocessable_entity();
        }

        auth_base::update_password($email, $password);
        return 'success';
    }
}
