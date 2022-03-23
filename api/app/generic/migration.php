<?php

/**
 * migration generic class
 *
 * @author Tomilin Dmitriy <bankastudio@gmail.com>
 * @package
 */

namespace generic;

use lib\dba;
use model\migration as mmigration;


class migration
{
    /**
     * @var string unical migration name
     */
    protected $migration_id;
    protected $comment = NULL;

    /**
     * migration up method
     * @return bool
     */
    protected function up()
    {
        return false;
    }

    /**
     * migration down method
     * @return bool
     */
    protected function down()
    {
        return false;
    }


    /**
     * public up method
     * @return bool
     */
    final public function do_up()
    {
        if (!$this->up())
            return false;
        mmigration::register_migration($this->migration_id, $this->comment);
        return true;
    }

    /**
     * public down method
     * @return bool
     */
    final public function do_down()
    {
        if (!$this->down())
            return false;

        mmigration::unregister_migration($this->migration_id);
        return true;
    }

    /**
     * @param $q - query separated by ';'
     * @return bool
     */
    protected function bunch_query($q)
    {
        $query = explode(";", $q);

        foreach ($query as $qu) {
            $qu = trim($qu);
            if (!$qu)
                continue;
            if (!dba::query($qu))
                return false;
        }
        return true;
    }

    /**
     * @param string $migration_id
     */
    public function __construct($migration_id)
    {
        $this->migration_id = $migration_id;
    }
}
