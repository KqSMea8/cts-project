<?php 

class Comm_Weibo_Exception_Api extends Comm_Exception_Program{
    public $error_code;
    
	public function __construct($message, $code = ''){
	    $this->error_code = $code;
		parent::__construct($message);
	}
	public function debug_code(){
	    return $this->error_code;
	}
}