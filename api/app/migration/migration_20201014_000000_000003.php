<?php
/*
*   migration 20201016_192800_127873
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201014_000000_000003 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "CREATE TABLE `reg_codes` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(45) NULL,
            `code` VARCHAR(45) NULL,
            `expire_at` DOUBLE NULL,
            PRIMARY KEY (`id`));";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE `reg_codes` ;";
        if (!DB::query($query))
            return false;

        return true;
    }
}
