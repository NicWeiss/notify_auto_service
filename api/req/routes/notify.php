<?php

dispatcher:: add('api/notify-new', array(
    'control_class' => 'control\notify',
    'control_function' => 'add',
    'ember_model' => 'notifyNew'
));

dispatcher:: add('api/notify', array(
    'control_class' => 'control\notify',
    'ember_model' => 'notify'
));

dispatcher:: add('api/notify/(\d+)', array(
    'control_class' => 'control\notify',
    'ember_model' => 'notify'
));