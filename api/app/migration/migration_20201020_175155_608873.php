<?php
/*
*   migration 20201020_175155_608873
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201020_175155_608873 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `notify`
                CHANGE COLUMN `acceptors` `notify_acceptors_id` INT NULL DEFAULT NULL ;
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
