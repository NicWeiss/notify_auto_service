<?php
/*
*   migration 20201016_192800_127873
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20201014_000000_000004 extends migration
{

    protected $comment = 'SET SQL_SAFE_UPDATES';

    protected function up()
    {
        $query = "SET SQL_SAFE_UPDATES = 0;";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "SET SQL_SAFE_UPDATES = 1;";
        if (!dba::query($query))
            return false;

        return true;
    }
}
