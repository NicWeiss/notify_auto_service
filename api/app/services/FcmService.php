<?php

/**
 * class worker
 * отвечает за актуализацию задач для worker
 */

namespace services;

use model\SystemModel;
use model\AcceptorModel;
use model\UserModel;

class FcmService
{
    var $acceptor = null;
    var $push_system_id = null;
    var $user = null;


    function __construct($user_id)
    {
        $this->user = UserModel::get($user_id);
        $this->push_system_id = SystemModel::get_system_by_type('push')['id'];
        $this->acceptor = AcceptorModel::get_one_acceptor_by_system($this->push_system_id, $this->user);
    }

    private function get_tokens()
    {
        $fcm_tokens = [];

        if ($this->acceptor) {
            if ($this->acceptor['account']) {
                $fcm_tokens = explode(';', $this->acceptor['account']);
            }
        }

        return $fcm_tokens;
    }

    private function set_tokens($fcm_tokens)
    {
        $account_string = implode(";", $fcm_tokens);

        if ($this->acceptor) {
            $acceptor = $this->acceptor;
            $acceptor['account'] = $account_string;
            $acceptor = AcceptorModel::update_acceptor($acceptor['id'], $acceptor, $this->user);
        } else {
            $acceptor = array(
                'user_id' => $this->user['id'],
                'name' => 'Push',
                'system_id' => $this->push_system_id,
                'is_system' => true,
                'account' => $account_string
            );
            $acceptor = AcceptorModel::create_acceptor($acceptor, $this->user);
        }

        $this->acceptor = $acceptor;
    }

    public function add_fcm_token($new_fcm_token)
    {
        $fcm_tokens = $this->get_tokens();

        if (!in_array($new_fcm_token, $fcm_tokens)) {
            array_push($fcm_tokens, $new_fcm_token);
        }

        $this->set_tokens($fcm_tokens);
    }

    public function remove_fcm_token($token)
    {
        $fcm_tokens = $this->get_tokens();

        $to_remove = array($token);
        $new_fcm_tokens = array_diff($fcm_tokens, $to_remove);

        $this->set_tokens($new_fcm_tokens);
    }
}
