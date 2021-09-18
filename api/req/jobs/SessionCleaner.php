<?php

namespace jobs;

use helpers\Logger as Logger;
use \model\auth_base as SessionModel;

final class SessionCleaner
{
    public static function run()
    {
        Logger::info("Run Session cleaner");
        SessionModel::delete_outdated_sessions();
    }
}
