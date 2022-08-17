<?php
/*
*   migration 20210604_202840_467671
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20210604_202840_467671 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "CREATE TABLE `date_operations` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `target_date` varchar(1000) NULL,
            `complete_date` varchar(1000) NULL,
            `type` varchar(100) NULL,
            `worker_id` VARCHAR(100) NULL,
        PRIMARY KEY (`id`));";

        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE 'date_operations';";
        if (!DB::query($query))
            return false;

        return true;
    }
}
