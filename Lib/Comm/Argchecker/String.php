<?php

class Comm_Argchecker_String{
	
	/**
	 * 是否可打印
	 * 
	 * This function is compatible with multi-bytes utf-8.
	 * 
	 * @param string $string
	 * @return bool
	 */
	static protected function safechars($string){
		$string = (string) $string;
		for($i = 0, $i_count = strlen($string); $i < $i_count; $i ++){
			$char_value = ord($string{$i});
			if(($char_value < 32 && ($char_value !== 13 && $char_value !== 10 && $char_value !== 9)) || $char_value == 127){
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 默认规则
	 * 
	 * @param string $data
	 * @return bool
	 */
	static public function basic($data){
		return self::safechars($data);
	}
	
	/**
	 * 可打印字符
	 * 
	 * @param string $string
	 * @param bool $utf8_compatible 可选。如果为真，则认为多字节utf-8(包含0x80~0xFF)也为可打印字符。否则，该函数只允许包含32~126的之间的字符。
	 * @return bool
	 */
	static public function printable($string, $utf8_compatible = true){
		$string = (string) $string;
		for($i = 0, $i_count = strlen($string); $i < $i_count; $i ++){
			$char_value = ord($string{$i});
			if($char_value < 32 || $char_value === 127 || !$utf8_compatible && $char_value > 127){
				return false;
			}
		}
		return true;
	}
	
	static public function max($data, $length){
		return strlen($data) <= $length;
	}
	
	static public function min($data, $length){
		return strlen($data) >= $length;
	}
	
	static public function width_max($data, $length){
		return mb_strwidth($data, 'utf-8') <= $length;
	}
	
	static public function width_min($data, $length){
		return mb_strwidth($data, 'utf-8') >= $length;
	}
	
	/**
	 * use regular expression to validating
	 * 
	 * @param string $data
	 * @param string $regular_expression
	 * @return bool
	 */
	static public function preg($data, $regular_expression){
		return (bool)preg_match($regular_expression, $data);
	}
	
	/**
	 * Alias of Comm_Argchecker_String::preg()
	 * 
	 * @see Comm_Argchecker_String::preg()
	 * @param string $data
	 * @param string $regular_expression
	 */
	static public function re($data, $regular_expression){
		return self::preg($data, $regular_expression);
	}
	
	static public function charslist($data, $charlist){
		return !trim($data, $charlist);
	}
	
	static public function num($data){
		return (bool)self::charslist($data, '0123456789'); 
	}
	
	static public function alnum($data){
		return self::preg($data, '/^[a-z0-9]*$/iD');
	}
	
	static public function alpha($data){
		return self::preg($data, '/^[a-z]*$/iD');
	}
	
	static public function lower($data){
		return self::preg($data, '/^[a-z]*$/D');
	}
	
	static public function upper($data){
		return self::preg($data, '/^[A-Z]*$/D');
	}
	
	static public function hex($data){
		return self::preg($data, '/^[a-f0-9]*$/iD');
	}
}