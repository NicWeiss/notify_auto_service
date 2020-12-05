<?php

dispatcher:: add('api/acceptor-new', array(
    'control_class' => 'control\acceptor',
    'control_function' => 'add',
    'ember_model' => 'acceptorNew'
));

dispatcher:: add('api/acceptors', array(
    'control_class' => 'control\acceptor',
    'ember_model' => 'acceptor'
));

dispatcher:: add('api/acceptors/(\d+)', array(
    'control_class' => 'control\acceptor',
    'ember_model' => 'acceptor'
));