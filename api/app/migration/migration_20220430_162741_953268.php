<?php
/*
*   migration 20220430_162741_953268
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20220430_162741_953268 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `session` ADD client varchar(1000) NULL;";
        if (!DB::query($query))
            return false;
        $query = "ALTER TABLE `session` ADD `location` varchar(1000) NULL;";
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
