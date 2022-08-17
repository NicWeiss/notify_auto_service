<?php
/*
*   migration 20201020_175353_684829
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20201020_175353_684829 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "CREATE TABLE `notify_acceptors` (
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `notify_id` INT NULL,
                  `acceptor_id` INT NULL,
                  PRIMARY KEY (`id`));
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
