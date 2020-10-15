<?php

dispatcher :: add('api/acceptor-new', array(
    'control_class' => 'control\acceptor',
    'control_function' => 'add',
    'is_ember_model' => True
));
