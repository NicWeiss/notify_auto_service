<?php

namespace jobs;

use helpers\Logger as Logger;
use \model\AuthModel;

final class SessionCleaner
{
    public static function run()
    {
        Logger::info("Run Session cleaner");
        AuthModel::delete_outdated_sessions();
    }
}
