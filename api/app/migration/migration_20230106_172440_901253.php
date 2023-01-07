<?php
/*
*   migration 20230106_172440_901253
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20230106_172440_901253 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `system` ADD COLUMN `is_system` BOOL DEFAULT false NULL;";
        if (!DB::query($query))
            return false;
        $query = "INSERT INTO `system` (`name`, `is_enable`, `help`, `type`, `is_system`) VALUES ('Push', '1', '', 'push', 1);";
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
