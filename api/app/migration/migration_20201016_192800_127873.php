<?php
/*
*   migration 20201016_192800_127873
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201016_192800_127873 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "CREATE TABLE `acceptor` (
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR(45) NULL,
                  `system_id` VARCHAR(45) NULL,
                  `account` VARCHAR(45) NULL,
                  `status` BOOLEAN,
                  PRIMARY KEY (`id`));
                ";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE `acceptor`;";
        if (!DB::query($query))
            return false;

        return true;
    }
}
