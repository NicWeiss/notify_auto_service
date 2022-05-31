<?php
/*
*   migration 20201016_233734_432650
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20201016_233734_432650 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `notifier`.`system` ADD COLUMN `type` VARCHAR(45) NULL AFTER `help`;";
        if (!dba::query($query))
            return false;
        $query = "UPDATE `notifier`.`system` SET `type` = 'tg' WHERE (`name` = 'Telegram');";
        if (!dba::query($query))
            return false;
        $query = "UPDATE `notifier`.`system` SET `type` = 'email' WHERE (`name` = 'Email');";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "ALTER TABLE `notifier`.`system` DROP COLUMN `type`;";
        if (!dba::query($query))
            return false;

        return true;
    }
}
