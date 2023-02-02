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
        $this->assertEquals(1, $acceptor['system_id']);
        $this->assertEquals(1, $acceptor['account']);
        $this->assertEquals('test_acceptor', $acceptor['name']);
        $this->assertEquals('1', $acceptor['user_id'], 'user_id is incorrect');
    }
}
