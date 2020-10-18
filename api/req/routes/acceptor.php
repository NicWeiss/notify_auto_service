<?php

dispatcher:: add('api/acceptor-new', array(
    'control_class' => 'control\acceptor',
    'control_function' => 'add',
    'ember_model' => 'acceptorNew'
));

dispatcher:: add('api/acceptor', array(
    'control_class' => 'control\acceptor',
    'ember_model' => 'acceptor'
));

//dispatcher:: add('api/acceptor/(\d+)', array(
//    'control_class' => 'control\acceptor',
//    'control_function' => 'get',
//    'ember_model' => 'acceptor'
//));