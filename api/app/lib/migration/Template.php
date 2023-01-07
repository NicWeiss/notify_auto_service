<?php

namespace lib\migration;

class Template
{

    public static function get_empty_migration($id)
    {
        return <<<CLASS
<?php
/*
*   migration $id
*/

namespace migration;
use generic\Migration;
use lib\DB;


final class migration_$id extends migration
{

    protected \$comment = 'No comment';

    protected function up(){
        \$query ="";
        if(!DB::query(\$query))
            return false;

        return true;
    }

    protected function down(){
        \$query ="";
        if(!DB::query(\$query))
            return false;

        return true;
    }
}
CLASS;
    }
}
