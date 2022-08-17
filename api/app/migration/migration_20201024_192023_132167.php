<?php
/*
*   migration 20201024_192023_132167
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201024_192023_132167 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `system`
                        CHANGE COLUMN `help` `help` LONGTEXT NULL DEFAULT NULL ;
                        ";
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
