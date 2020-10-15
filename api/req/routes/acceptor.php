<?php

dispatcher :: add('api/acceptor-new', array(
    'control_class' => 'control\acceptor',
    'control_function' => 'add'
));

//dispatcher :: add('api/auth/sign_up', array(
//    'control_class' => 'control\auth',
//    'control_function' => 'sign_up'
//));
//
//dispatcher :: add('api/auth/get_code', array(
//    'control_class' => 'control\auth',
//    'control_function' => 'get_code'
//));
