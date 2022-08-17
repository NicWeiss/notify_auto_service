<?php

dispatcher::add('api/notifies', array(
    'control_class' => 'control\Notify',
    'ember_model' => 'notify'
));

dispatcher::add('api/notifies/(\d+)', array(
    'control_class' => 'control\Notify',
    'ember_model' => 'notify'
));

dispatcher::add('api/notifies/delete_by_category_id', array(
    'control_class' => 'control\Notify',
    'control_function' => 'delete_by_category_id'
));

dispatcher::add('api/notifies/reset_from_category_id', array(
    'control_class' => 'control\Notify',
    'control_function' => 'reset_from_category_id'
));
