<?php

use PHPUnit\Framework\TestCase;
use model\AcceptorModel;


final class AcceptorTest extends TestCase
{

    public function testCreate(): void
    {
        $user = ['id' => '1'];
        $acceptor_data = [
            'name' => 'test_acceptor',
            'system_id' => 1,
            'account' => 1
        ];

        $acceptor = AcceptorModel::create_acceptor($acceptor_data, $user);

        $this->assertEquals(boolval($acceptor), true);
        $this->assertEquals($acceptor['system_id'], 1);
        $this->assertEquals($acceptor['account'], 1);
        $this->assertEquals($acceptor['name'], 'test_acceptor');
        $this->assertEquals($acceptor['user_id'], '1');
    }
}
