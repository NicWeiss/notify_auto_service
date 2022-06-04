<?php
/*
*   migration 20201024_192122_529969
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20201024_192122_529969 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "UPDATE `system` SET `help` = ' Нужен id: <br>Для этого откройте бота
                    <a target=\"_blank\" href=\"http://t.me/my_id_bot\">What\'s my ID</a>, зaпустите его.
                    Cкопируйте код. <br><span style=\"color: #e05959;\"><br>
                    Внимание, для получения оповещений необходимо подписаться на бота
                    </span> <a target=\"_blank\" href=\"http://t.me/WeissNotifierSystem_bot\"> Notifier</a>
                    ' WHERE `type` = 'tg';";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "";
        if (!dba::query($query))
            return false;

        return true;
    }
}
