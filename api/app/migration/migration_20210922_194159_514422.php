<?php
/*
*   migration 20210922_194159_514422
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20210922_194159_514422 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE notifier.notify DROP COLUMN time_zone_offset;";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "ALTER TABLE `notifier`.`notify`  ADD COLUMN `time_zone_offset` VARCHAR(45) NULL AFTER `status`;";
        if (!dba::query($query))
            return false;

        return true;
    }
}
