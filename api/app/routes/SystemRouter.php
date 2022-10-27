<?php

dispatcher::add('api/systems', array(
    'control_class' => 'control\System',
    'ember_model' => 'system'
));

dispatcher::add('api/systems/(\d+)', array(
    'control_class' => 'control\System',
    'ember_model' => 'systems'
));
