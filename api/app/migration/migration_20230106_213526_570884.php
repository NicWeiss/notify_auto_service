<?php
/*
*   migration 20230106_213526_570884
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20230106_213526_570884 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE notifier.acceptor MODIFY COLUMN account LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL;";
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
