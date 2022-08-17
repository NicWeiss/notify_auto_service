<?php
/*
*   migration 20220430_142010_289228
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20220430_142010_289228 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `user` ADD timezone varchar(100) NULL;";
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
