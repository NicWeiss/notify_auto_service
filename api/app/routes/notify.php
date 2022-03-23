<?php

dispatcher::add('api/notifies', array(
    'control_class' => 'control\notify',
    'ember_model' => 'notify'
));

dispatcher::add('api/notifies/(\d+)', array(
    'control_class' => 'control\notify',
    'ember_model' => 'notify'
));

dispatcher::add('api/notifies/delete_by_category_id', array(
    'control_class' => 'control\notify',
    'control_function' => 'delete_by_category_id'
));
