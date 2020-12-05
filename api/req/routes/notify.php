<?php

dispatcher:: add('api/notifies', array(
    'control_class' => 'control\notify',
    'ember_model' => 'notify'
));

dispatcher:: add('api/notifies/(\d+)', array(
    'control_class' => 'control\notify',
    'ember_model' => 'notify'
));