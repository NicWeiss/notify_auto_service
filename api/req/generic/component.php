<?php

namespace generic;

use lib\request;


class component{
    /**
     * component call parameters array
     * @var array
     */
    protected $code = 200;

    protected function def_return($data){
        return [
            'data' => $data,
            'code' => $this->code
        ];
    }

    protected function has_no_permission(){
        $this->code = 403;
    }

    protected function not_found(){
        $this->code = 404;
    }
}