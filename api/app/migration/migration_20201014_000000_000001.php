<?php
/*
*   migration 20201016_192800_127873
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201014_000000_000001 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "CREATE TABLE `user` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `user` VARCHAR(45) NULL,
            `email` VARCHAR(45) NULL,
            `password` VARCHAR(45) NULL,
            PRIMARY KEY (`id`));";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE `user` ;";
        if (!DB::query($query))
            return false;

        return true;
    }
}
