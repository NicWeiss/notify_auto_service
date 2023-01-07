<?php

dispatcher::add('api/acceptors', array(
    'control_class' => 'control\Acceptor',
    'ember_model' => 'acceptor'
));

dispatcher::add('api/acceptors/(\d+)', array(
    'control_class' => 'control\Acceptor',
    'ember_model' => 'acceptor'
));

dispatcher::add('api/acceptors/update_push_tokens', array(
    'control_class' => 'control\Acceptor',
    'control_function' => 'update_push_acceptor'
));
