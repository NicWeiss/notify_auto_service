<?php

/**
 * class auth
 * отвечает за авторизацию и регистрацию пользователей
 */

namespace control;

use cfg;
use generic\BaseController;
use helpers\Logger;
use lib\email;
use lib\request;
use model\AuthModel;

class auth extends BaseController
{
    private static $ONE_WEEK = 604800;
    private static $ONE_HUNDRED_YEARS = 338688000;

    private static function getLocation()
    {
        $host = request::$host;
        $api_key = cfg::$siteconf['ip_location_provider_token'];
        $location_api_response = null;
        $default_location = "{\"ip\": \"$host\"}";

        if (cfg::$siteconf['ip_location_provider_token']) {
            try {
                $url = "https://api.ipdata.co/$host?api-key=$api_key&fields=ip,city,region,country_name,continent_name";
                $curl_query = curl_init($url);
                curl_setopt($curl_query, CURLOPT_RETURNTRANSFER, true);
                $location_api_response = curl_exec($curl_query);
                curl_close($curl_query);
            } catch (\Throwable $th) {
                Logger::error('Location Api not availible');
                $location_api_response = null;
            }

            if (!preg_match("/ip/", $location_api_response)) {
                $location_api_response = null;
            }
        }

        return $location_api_response ? $location_api_response : $default_location;
    }

    public static function login()
    {
        /*
         * Метод авторизации пользователя
         *      1.  Будет проверен проверен логин и пароль. если тользователь не найден - 403
         *      2.  Если пользователь найден, то будет создана сессия и отправлена на фронт
         */
        $location_api_response = null;

        $email = self::$request_json['email'];
        $client = json_encode(self::$request_json['client']);
        $location = self::getLocation();
        $is_from_mobile = self::$request_json['is_from_mobile'];
        $password = md5(self::$request_json['password']);

        $user = AuthModel::get_user($email, $password);

        if (!$user) {
            throw self::has_no_permission();
        }

        $session_id = md5(uniqid(rand(), true));
        $session_life_time = $is_from_mobile ? self::$ONE_HUNDRED_YEARS : self::$ONE_WEEK;
        $current_date = date_create();

        $session = [
            'user_id' => $user['id'],
            'session' => $session_id,
            'client' => $client,
            'location' => $location,
            'expire_at' => (date_timestamp_get($current_date) + $session_life_time)
        ];

        AuthModel::set_session($session);

        return ['name' => $user['name'], 'session' => $session_id];
    }

    public static function get_code()
    {
        /*
         * Метод отправки кода подтверждения
         *      1.  Будет проверен код и если он выслан и активен, то выход
         *      2.  Если подходящего кода нет, то будет создан новый, сохранён в базу и выслан
         */
        $email = self::$request_json['email'];
        $code = random_int(1000, 9999);
        $date = date_create();

        if (AuthModel::check_user_exists($email)) {
            throw self::unprocessable_entity();
        }
        if (AuthModel::is_reg_code_exist($email, date_timestamp_get($date))) {
            throw self::has_no_permission();
        }

        AuthModel::create_code(['email' => $email, 'code' => $code, 'expire_at' => (date_timestamp_get($date) + 300)]);

        $is_send = email::send([
            'to' => $email,
            'title' => 'Код подтверждения',
            'text' => 'Ваш код подтверждения: ' . $code
                . '. Наберите его в поле ввода длязавершения регистрации'
        ]);
        if (!$is_send) {
            throw self::critical_error();
        }

        return 'code sended';
    }

    public static function sign_up()
    {
        $date = date_create();

        $accepted_code = self::$request_json['code'];
        $email = self::$request_json['email'];
        $client = json_encode(self::$request_json['client']);
        $location = self::getLocation();

        $sended_code = AuthModel::get_reg_code($email, date_timestamp_get($date));
        if (!$sended_code) {
            throw self::has_no_permission();
        }

        if ($accepted_code == $sended_code) {
            $session = md5(uniqid(rand(), true));
            $password = md5(self::$request_json['password']);

            if (AuthModel::check_user_exists($email)) {
                throw self::unprocessable_entity();
            }

            $user = [
                'name' => self::$request_json['name'],
                'password' => $password,
                'email' => $email
            ];
            $user_id = AuthModel::create_user($user);

            if (!$user_id) {
                throw self::critical_error();
            }


            $data = [
                'user_id' => $user_id,
                'session' => $session,
                'client' => $client,
                'location' => $location,
                'expire_at' => (date_timestamp_get($date) + 3600)
            ];

            AuthModel::set_session($data);

            return $session;
        }

        throw self::has_no_permission();
    }

    public static function restore()
    {
        $date = date_create();

        $email = self::$request_json['email'];
        if (!$email) {
            throw self::unprocessable_entity();
        }

        $restore_hash = md5(strval(random_int(1000, 9999) * random_int(1000, 9999)));


        if (!AuthModel::check_user_exists($email)) {
            throw self::unprocessable_entity();
        }
        if (AuthModel::is_restore_code_exist($email, date_timestamp_get($date))) {
            throw self::has_no_permission();
        }

        AuthModel::create_restore_code([
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
        $code = self::$request_json['code'];

        if (!AuthModel::is_restore_code_exist_by_code($code, date_timestamp_get($date))) {
            throw self::unprocessable_entity();
        }

        return 'exist';
    }

    public static function restore_password()
    {
        $date = date_create();
        $code = self::$request_json['code'];
        $password = md5(self::$request_json['password']);

        $email = AuthModel::get_email_by_restore_code($code, date_timestamp_get($date));

        if (!$email) {
            throw self::unprocessable_entity();
        }

        AuthModel::update_password($email, $password);
        return 'success';
    }

    public static function change_password()
    {
        $current_password = md5(self::$request_json['currentPass']);
        $new_password = md5(self::$request_json['newPass']);
        $new_password_repeate = md5(self::$request_json['newPassRepeate']);

        if (
            !$new_password ||
            ($new_password != $new_password_repeate) ||
            (self::$user['password'] != $current_password)
        ) {
            throw self::unprocessable_entity();
        }

        AuthModel::update_password(self::$user['email'], $new_password);
        return 'success';
    }
}
