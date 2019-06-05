<?php

class Comm_Weibo_Exception_SinaSSO extends Comm_Exception_Program{
    public $error_code;
    public function __construct($message, $error_code = ''){
        parent::__construct($message);
        $this->code = $error_code;
    }
}