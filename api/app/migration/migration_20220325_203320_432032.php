<?php
/*
*   migration 20220325_203320_432032
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20220325_203320_432032 extends migration
{

    protected $comment = 'расширение длины описания уведомления';

    protected function up()
    {
        $query = "ALTER TABLE notify MODIFY COLUMN `text` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL;";
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
