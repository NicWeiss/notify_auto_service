<?php
/*
*   migration 20201024_154605_566593
*/

namespace migration;
use generic\migration;
use lib\dba;
    
    
final class migration_20201024_154605_566593 extends migration
{

    protected $comment = 'No comment';

    protected function up(){
        $query ="ALTER TABLE `notifier`.`notify`  ADD COLUMN `time_zone_offset` VARCHAR(45) NULL AFTER `status`;";
        if(!dba::query($query))
            return false;

        return true;
    }
    
    protected function down(){
        $query ="ALTER TABLE `notifier`.`notify` DROP COLUMN `time_zone_offset`;";
        if(!dba::query($query))
            return false;

        return true;
    }
} 
