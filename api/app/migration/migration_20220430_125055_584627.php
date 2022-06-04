<?php
/*
*   migration 20220430_125055_584627
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20220430_125055_584627 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE category ADD is_hidden BOOL DEFAULT false NOT NULL;";
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
