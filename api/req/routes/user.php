<?php

dispatcher::add('api/user', array(
    'control_class' => 'control\user',
    'ember_model' => 'user'
));

dispatcher::add('api/users/(\d+)', array(
    'control_class' => 'control\user',
    'ember_model' => 'user'
));