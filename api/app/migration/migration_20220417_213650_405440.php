<?php
/*
*   migration 20220417_213650_405440
*/

namespace migration;

use generic\migration;
use lib\dba;


final class migration_20220417_213650_405440 extends migration
{

    protected $comment = 'Convert data into new format';

    protected function up()
    {
        $notify = 'notifier.notify';

        $sql = "SELECT * FROM $notify  WHERE date != '' ;";
        dba::query($sql);
        $notify_list = dba::fetch_assoc_all();

        foreach ($notify_list as $key => $value) {
            if (preg_match('/[0-9]{2}\.[0-9]{2}\.[0-9]{4}/', $value['date'])) {
                $notify_id = $value['id'];
                $date = explode('.', $value['date']);

                if ($date) {
                    $new_date = $date[2] . '-' . $date[0] . '-' . $date[1];
                    $sql = "UPDATE $notify SET `date`= '$new_date' WHERE `id`='$notify_id'";

                    dba::query($sql);
                }
            }
        }

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
