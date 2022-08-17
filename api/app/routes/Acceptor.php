<?php

dispatcher::add('api/acceptors', array(
    'control_class' => 'control\Acceptor',
    'ember_model' => 'acceptor'
));

dispatcher::add('api/acceptors/(\d+)', array(
    'control_class' => 'control\Acceptor',
    'ember_model' => 'acceptor'
));
