<?php
/*
*   migration 20201016_220559_070701
*/

namespace migration;
use generic\migration;
use lib\dba;
    
    
final class migration_20201016_220559_070701 extends migration
{

    protected $comment = 'Firsts_systems';

    protected function up(){
        $query ="INSERT INTO `system` (`name`, `is_enable`, `help`) VALUES ('Telegram', '1', 'Нужен id');";
        if(!dba::query($query))
            return false;
        $query ="INSERT INTO `system` (`name`, `is_enable`, `help`) VALUES ('Email', '1', 'Укажите адрес электронной почты');";
        if(!dba::query($query))
            return false;

        return true;
    }
    
    protected function down(){
        return true;
    }
}
