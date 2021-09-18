<?php

namespace jobs;

use \model\auth_base as SessionModel;

final class SessionCleaner
{
    public static function run()
    {
        SessionModel::delete_outdated_sessions();
    }
}
