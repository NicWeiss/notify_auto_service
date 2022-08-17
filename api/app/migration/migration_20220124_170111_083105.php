<?php
/*
*   migration 20220124_170111_083105
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20220124_170111_083105 extends migration
{
    protected $comment = 'Смена имени поля';

    protected function up()
    {
        $query = "ALTER TABLE `user` CHANGE `user` name varchar(45);";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "ALTER TABLE `user` CHANGE `name` user varchar(45);";
        if (!DB::query($query))
            return false;

        return true;
    }
}
