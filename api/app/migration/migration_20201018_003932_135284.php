<?php
/*
*   migration 20201018_003932_135284
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201018_003932_135284 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE `acceptor` ADD COLUMN `user_id` INT NULL AFTER `status`;";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "ALTER TABLE `acceptor` DROP COLUMN `user_id`;";
        if (!DB::query($query))
            return false;

        return true;
    }
}
