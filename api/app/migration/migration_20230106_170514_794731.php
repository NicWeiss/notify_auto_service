<?php
/*
*   migration 20230106_170514_794731
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20230106_170514_794731 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `acceptor` ADD COLUMN `is_system` BOOL DEFAULT false NULL;";
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
