<?php
/*
*   migration 20210923_203121_400182
*/

namespace migration;

use generic\Migration;
use lib\DB;


final class migration_20210923_203121_400182 extends migration
{

    protected $comment = 'Таблица для категорий уведомлений';

    protected function up()
    {
        $query = "CREATE TABLE category (
                    id int NOT NULL,
                    user_id INTEGER NOT NULL,
                    name varchar(100) NOT NULL
                )
                ENGINE=InnoDB
                DEFAULT CHARSET=utf8mb4
                COLLATE=utf8mb4_0900_ai_ci;
                ";
        if (!DB::query($query))
            return false;

        return true;
    }

    protected function down()
    {
        $query = "DROP TABLE `category`;";
        if (!DB::query($query))
            return false;

        return true;
    }
}
