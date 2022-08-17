<?php
/*
*   migration 20220430_125055_584627
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20220430_125055_584627 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE category ADD is_hidden BOOL DEFAULT false NOT NULL;";
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
