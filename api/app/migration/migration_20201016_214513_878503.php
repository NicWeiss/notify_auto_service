<?php
/*
*   migration 20201016_214513_878503
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20201016_214513_878503 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "CREATE TABLE `system` (
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR(45) NULL,
                  `is_enable` BOOLEAN,
                  `help` VARCHAR(45) NULL,
                  PRIMARY KEY (`id`));";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE `system`;";
        if (!dba::query($query))
            return false;

        return true;
    }
}
