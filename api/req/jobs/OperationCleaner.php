<?php

namespace jobs;

use helpers\Logger as Logger;
use \model\watcher_model as OperationsModel;

final class OperationCleaner
{
    public static function run()
    {
        Logger::info("Run Operation cleaner");
        OperationsModel::delete_all_done_operations_without_last();
    }
}
