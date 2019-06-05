<?php
class Comm_Argchecker_DataList {
	protected static $delimiter = '';
	protected static $temp_data = '';
	
    public static function datalist($data, $type, $delimiter, $rules = '', $is_needed = 1, $must_correct = 1, $default = null){
    	self::$temp_data = '';
    	
    	$type = strtolower($type);
    	Comm_Assert::true(in_array($type, array('int', 'float', 'enum', 'string'), true), 'basic type should be only int,float,enum,string.');
    	
    	$delimiter = Comm_Argchecker::extract_escaped_chars($delimiter);
    	$rules = Comm_Argchecker::extract_escaped_chars($rules);
    	$default = $default === NULL ? NULL : Comm_Argchecker::extract_escaped_chars($default);
    	$is_needed = intval($is_needed);
    	$must_correct = intval($is_needed);
    	
    	Comm_Assert::true(is_string($delimiter) && $delimiter, 'delimiter should be a valid string');
    	self::$delimiter = $delimiter;
    	self::$temp_data = explode($delimiter, $data);
    	
    	foreach (self::$temp_data as $key => $value){
    		Comm_Argchecker::$type($value, $rules, $is_needed, $must_correct, $default);
    	}
    	
    	return true;
    }
    
    public static function max($data, $length){
    	Comm_Assert::true(is_array(self::$temp_data), 'must use type rule to define the subtype and rules first of all');
    	return count(self::$temp_data) <= intval($length);
    }
    
    public static function min($data, $length){
    	Comm_Assert::true(is_array(self::$temp_data), 'must use type rule to define the subtype and rules first of all');
    	return count(self::$temp_data) >= intval($length);
    }
    
    public static function unique($data){
    	Comm_Assert::true(is_array(self::$temp_data), 'must use type rule to define the subtype and rules first of all');
    	return count(array_unique(self::$temp_data)) === count(self::$temp_data); 
    }
}