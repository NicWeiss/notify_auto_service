<?php
/*
*   migration 20201020_164724_399668
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20201020_164724_399668 extends migration
{

    protected $comment = 'No comment';

    protected function up()
    {
        $query = "
        CREATE TABLE `notify` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `user_id` INT NULL,
              `name` VARCHAR(45) NULL,
              `text` VARCHAR(45) NULL,
              `periodic` VARCHAR(45) NULL,
              `day_of_week` VARCHAR(45) NULL,
              `date` DATE NULL,
              `time` TIME NULL,
              `acceptors` LONGTEXT NULL,
              PRIMARY KEY (`id`));
            ";
        if (!dba::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE `notify`;";
        if (!dba::query($query))
            return false;

        return true;
    }
}
