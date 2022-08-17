<?php
/*
*   migration 20201020_191310_347332
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201020_191310_347332 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `notify`
                CHANGE COLUMN `notify_acceptors_id` `status` TINYINT NULL DEFAULT 1 ;
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
