<?php
/*
*   migration 20220325_203558_775296
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20220325_203558_775296 extends migration
{

    protected $comment = 'Увеличение длины имени';

    protected function up()
    {
        $query = "ALTER TABLE notify MODIFY COLUMN name varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL;";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "";
        if (!dba::query($query))
            return false;

        return true;
    }
}
