<?php
/*
*   migration 20230202_203051_384720
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20230202_203051_384720 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "ALTER TABLE user ADD UNIQUE (email);";
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
