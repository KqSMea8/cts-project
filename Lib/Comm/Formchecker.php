<?php

class Comm_FormChecker implements ArrayAccess{
	protected $rules = array();
	protected $values = array();
	protected $binded_exceptions = array();
	
	/**
	 * 
	 * @param array $rules
	 * @return Comm_FormChecker
	 */
	public static function validate(array $rules, array $data){
		$checker = new self($rules);
		$checker->put($data);
		return $checker;
	}
	
	public function __construct(array $rules){
		$this->rules = $rules;
	}
	
	/**
	 * 
	 * @param array $data
	 * @throws Comm_FormChecker_FailException
	 * @return Comm_FormChecker
	 */
	public function put(array $data){
		foreach ($data as $k => $v){
			try{
				$this->validate_single_value($k, $v);
			}catch (Comm_Exception_Program $ex){
				if(isset($this->binded_exceptions[$k])){
					throw $this->binded_exceptions[$k];
				}else{
					throw new Comm_FormChecker_FailException($k);
				}
			}
		}
		return $this;
	}
	
	public function get($key, $default = null){
		if(!isset($this->values[$key])){
			throw new Comm_Exception_Program('Key not valid');
		}
		
		return $this->values[$key];
	}
	
	/**
	 * 为某一个key绑定一个指定的异常。当该key验证失败时，该异常会被抛出。
	 * 
	 * @param string $key
	 * @param Exception $exception
	 * @return Comm_FormChecker
	 */
	public function bind_exception($key, Exception $exception){
		$this->binded_exceptions[$key] = $exception;
		return $this;
	}
	
	public function offsetGet($key){
		return $this->get($key);
	}
	
	public function offsetExists($key){
		return false;
	}
	
	public function offsetSet($key, $rules){
		$this->rules[$key] = $rules;
	}
	
	public function offsetUnset($key){
		$this->rules[$key] = null;
	}
	
	protected function validate_single_value($key, $value){
		if(!isset($this->rules[$key]) || !is_array($this->rules[$key])){
			return ;
		}
		$rule = $this->rules[$key];
		$type = $rule[0];
		$rule[0] = $value;
		
		return call_user_func_array(array('Comm_Argchecker', $type), $rule);
	}
}