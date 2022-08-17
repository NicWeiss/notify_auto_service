<?php

dispatcher::add('api/user', array(
    'control_class' => 'control\User',
    'ember_model' => 'user'
));

dispatcher::add('api/users/(\d+)', array(
    'control_class' => 'control\User',
    'ember_model' => 'user'
));

dispatcher::add('api/user/delete', array(
    'control_class' => 'control\User',
    'control_function' => 'delete'
));
