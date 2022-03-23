<?php
/*
*   migration 20210604_202840_467671
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20210604_202840_467671 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "CREATE TABLE `notifier`.`date_operations` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `target_date` varchar(1000) NULL,
            `complete_date` varchar(1000) NULL,
            `type` varchar(100) NULL,
            `worker_id` VARCHAR(100) NULL,
        PRIMARY KEY (`id`));";

        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE 'notifier'.'date_operations';";
        if (!dba::query($query))
            return false;

        return true;
    }
}
