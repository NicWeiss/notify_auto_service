<?php
/*
*   migration 20220124_170111_083105
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20220124_170111_083105 extends migration
{
    protected $comment = 'Смена имени поля';

    protected function up()
    {
        $query = "ALTER TABLE notifier.`user` CHANGE `user` name varchar(45);";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "ALTER TABLE notifier.`user` CHANGE `name` user varchar(45);";
        if (!dba::query($query))
            return false;

        return true;
    }
}
