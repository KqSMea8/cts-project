<?php 

class Comm_FormChecker_FailException extends Comm_Exception_Program{
	public $field_name = '';
	public function __construct($field_name){
		parent::__construct('form check failed');
		$this->field_name = $field_name;
	}
}