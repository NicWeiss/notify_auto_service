<?php

dispatcher::add('api/sessions', array(
    'control_class' => 'control\session',
    'ember_model' => 'session'
));

dispatcher::add('api/sessions/(\d+)', array(
    'control_class' => 'control\session',
    'ember_model' => 'session'
));
