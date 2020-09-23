<?php
/**
 * ROUTE LIST
 */

dispatcher :: add('api/auth/login', array(
    'control_class' => 'control\auth',
    'control_function' => 'login'
));

dispatcher :: add('api/auth/sign_up', array(
    'control_class' => 'control\auth',
    'control_function' => 'sign_up'
));

dispatcher :: add('api/auth/get_code', array(
    'control_class' => 'control\auth',
    'control_function' => 'get_code'
));
