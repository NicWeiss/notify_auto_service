<?php

use PHPUnit\Framework\TestCase;

use model\SystemModel;
use model\AcceptorModel;
use model\UserModel;
use services\FcmService;


final class FcmTest extends TestCase
{

    public function testSet(): void
    {
        $user_data = ["name" => "test", "email" => "t@t.t", "password" => "test"];
        $user = UserModel::create_user($user_data);

        $fcm_service = new FcmService($user['id']);
        $fcm_service->add_fcm_token("qewrqwerqrweqwer");

        $push_system_id = SystemModel::get_system_by_type('push')['id'];
        $acceptor = AcceptorModel::get_one_acceptor_by_system($push_system_id, $user);
        $fcm_tokens = explode(';', $acceptor['account']);

        $this->assertEquals(1, count($fcm_tokens));
        $this->assertEquals("qewrqwerqrweqwer", $fcm_tokens[0]);
    }

    public function testRemove(): void
    {
        $user_data = ["name" => "test", "email" => "t2@t.t", "password" => "test"];
        $user = UserModel::create_user($user_data);
        $push_system_id = SystemModel::get_system_by_type('push')['id'];
        $acceptor = array(
            'user_id' => $user['id'],
            'name' => 'Push',
            'system_id' => $push_system_id,
            'is_system' => true,
            'account' => 'dfhthydfntr4'
        );
        $acceptor = AcceptorModel::create_acceptor($acceptor, $user);

        $fcm_service = new FcmService($user['id']);
        $fcm_service->remove_fcm_token('dfhthydfntr4');

        $acceptor = AcceptorModel::get_one_acceptor_by_system($push_system_id, $user);
        $fcm_tokens = explode(';', $acceptor['account']);

        $this->assertEquals(1, count($fcm_tokens), 'Wrong count of fcm tokens');
        $this->assertEquals('', $fcm_tokens[0]);
    }
}
