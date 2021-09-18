<?php

namespace jobs;

use \model\watcher_model as OperationsModel;

final class OperationCleaner
{
    public static function run()
    {
        OperationsModel::delete_all_done_operations_without_last();
    }
}
