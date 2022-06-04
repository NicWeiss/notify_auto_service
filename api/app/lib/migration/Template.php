<?php

namespace lib\migration;

class Template
{

    public static function get_empty_migration($id)
    {
        $template = <<<CLASS
<?php
/*
*   migration $id
*/

namespace migration;
use generic\migration;
use lib\dba;


final class migration_$id extends migration
{

    protected \$comment = 'No comment';

    protected function up(){
        \$query ="";
        if(!dba::query(\$query))
            return false;

        return true;
    }

    protected function down(){
        \$query ="";
        if(!dba::query(\$query))
            return false;

        return true;
    }
}
CLASS;
    }
}
