<?php
/*
*   migration 20201016_233734_432650
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201016_233734_432650 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `system` ADD COLUMN `type` VARCHAR(45) NULL AFTER `help`;";
        if (!DB::query($query))
            return false;
        $query = "UPDATE `system` SET `type` = 'tg' WHERE (`name` = 'Telegram');";
        if (!DB::query($query))
            return false;
        $query = "UPDATE `system` SET `type` = 'email' WHERE (`name` = 'Email');";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "ALTER TABLE `system` DROP COLUMN `type`;";
        if (!DB::query($query))
            return false;

        return true;
    }
}
