<?php
/*
*   migration 20210131_155610_380825
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20210131_155610_380825 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "CREATE TABLE `notifier`.`restore_codes` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(45) NULL,
            `code` VARCHAR(45) NULL,
            `expire_at` DOUBLE NULL,
            PRIMARY KEY (`id`));";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE `restore_codes` ;";
        if (!dba::query($query))
            return false;

        return true;
    }
}
