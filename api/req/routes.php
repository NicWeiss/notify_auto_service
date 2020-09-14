<?php
/**
 * ROUTE LIST
 */

dispatcher :: add('api/list', array(
    'control_class' => 'control\posts_list',
    'control_function' => 'get_list'
));
