<?php
/*
*   migration 20210925_111328_926258
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20210925_111328_926258 extends migration
{

    protected $comment = 'Поле для привязки уведомления к категории';

    protected function up()
    {
        $query = "ALTER TABLE notify ADD category_id int DEFAULT 0 NOT NULL;";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "";
        if (!DB::query($query))
            return false;

        return true;
    }
}
