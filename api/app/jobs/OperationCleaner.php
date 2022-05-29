<?php

namespace jobs;

use helpers\Logger as Logger;
use \model\WatcherModel;

final class OperationCleaner
{
    public static function run()
    {
        Logger::info("Run Operation cleaner");
        WatcherModel::delete_all_done_operations_without_last();
    }
}
