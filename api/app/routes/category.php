<?php

dispatcher::add('api/categories', array(
    'control_class' => 'control\category',
    'ember_model' => 'category'
));

dispatcher::add('api/categories/(\d+)', array(
    'control_class' => 'control\category',
    'ember_model' => 'category'
));
