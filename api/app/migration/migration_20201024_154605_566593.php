<?php
/*
*   migration 20201024_154605_566593
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201024_154605_566593 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `notify`  ADD COLUMN `time_zone_offset` VARCHAR(45) NULL AFTER `status`;";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "ALTER TABLE `notify` DROP COLUMN `time_zone_offset`;";
        if (!DB::query($query))
            return false;

        return true;
    }
}
