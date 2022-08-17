<?php

dispatcher::add('api/sessions', array(
    'control_class' => 'control\Session',
    'ember_model' => 'session'
));

dispatcher::add('api/sessions/(\d+)', array(
    'control_class' => 'control\Session',
    'ember_model' => 'session'
));
