<?php

dispatcher :: add('api/system', array(
    'control_class' => 'control\system',
    'control_function' => 'get_all',
    'ember_model' => 'system'
));
