<?php
/*
*   migration 20201020_181601_890862
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201020_181601_890862 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `notify`
                CHANGE COLUMN `date` `date` VARCHAR(45) NULL DEFAULT NULL ,
                CHANGE COLUMN `time` `time` VARCHAR(45) NULL DEFAULT NULL ;
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
