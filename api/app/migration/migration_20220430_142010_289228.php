<?php
/*
*   migration 20220430_142010_289228
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20220430_142010_289228 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE notifier.`user` ADD timezone varchar(100) NULL;";
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
