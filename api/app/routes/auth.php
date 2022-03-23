<?php

dispatcher::add('api/auth/login', array(
    'control_class' => 'control\auth',
    'control_function' => 'login'
));

dispatcher::add('api/auth/sign_up', array(
    'control_class' => 'control\auth',
    'control_function' => 'sign_up'
));

dispatcher::add('api/auth/get_code', array(
    'control_class' => 'control\auth',
    'control_function' => 'get_code'
));

dispatcher::add('api/auth/restore', array(
    'control_class' => 'control\auth',
    'control_function' => 'restore'
));

dispatcher::add('api/auth/restore/verify-restore-code', array(
    'control_class' => 'control\auth',
    'control_function' => 'verify_restore_code'
));


dispatcher::add('api/auth/restore/change-password', array(
    'control_class' => 'control\auth',
    'control_function' => 'change_password'
));
