<?php

/**
 * 自动调用计数器。
 * 
 * 自动计算count方法被调用次数。
 * 
 * Tutorial:
 * <code>
 * function a(){
 * 	Comm_Debug_CallerCounter::count();
 * }
 * a();
 * a();
 * 
 * echo 2 === Comm_Debug_CallerCounter::get('a');
 * 
 * class A{
 * 	static function a(){
 * 		return Comm_Debug_CallerCounter::count();
 * 	}
 * 
 *  function b(){
 *  	Comm_Debug_CallerCounter::count();
 *  }
 * }
 * 
 * $a_called_times = A::a();
 * 
 * echo 1 === $a_called_times;
 * echo 1 === Comm_Debug_CallerCounter::get(array(A, 'a'));
 * echo 1 === Comm_Debug_CallerCounter::get('A::a');
 * 
 * $a = new A();
 * $a->b();
 * $a->b();
 * $a->b();
 * 
 * echo 3 === Comm_Debug_CallerCounter::get(array(A, 'b'));
 * echo 3 === Comm_Debug_CallerCounter::get('A->b');
 * </code>
 * @author Rodin <luodan@staff.sina.com.cn>
 * @package Swift
 * @subpackage Debug
 *
 */
class Comm_Debug_CallerCounter {
	static public $is_enable = false;
	static protected $counters = array(); 
	
	/**
	 * 清除已有的全部计数器
	 */
	static public function reset(){
		self::$counters = array();
	}
	
	/**
	 * 根据指定的caller或者本方法的调用者来进行计数。
	 * 
	 * 注：本方法的调用者使用debug_backtrace()来获取。
	 * caller可以传入一个指定的字符串或者一个对象或者一个callback结构的数组。
	 * 如果是字符串，则直接使用该字符串作为计数标记。
	 * 如果是对象，则使用对象的类名作为计数标记。
	 * 如果是callback数组，如果调用者是类方法（静态方法），则采用 Class::method 作为caller标记字符串进行技术，如果调用者是实例方法，则采用 Class->method作为caller标记字符串。
	 * 
	 * 取出计数值时需要使用计数字符串来进行计数结果的获取。
	 * @param string|object|callback $caller 调用者的字符串表示，可以手动指定。可选，默认会取count()方法的调用者方法并字符串化。
	 */
	static public function count($caller = NULL){
		if(!self::$is_enable){
			return ;
		}
		
		if(is_null($caller)){
			$backtrace = debug_backtrace();
			$caller_info = $backtrace[1];
			$full_name = self::get_func_full_name_from_caller_info($caller_info);
		}else{
			$full_name = self::get_func_full_name($caller);
		}
		
		if(!isset(self::$counters[$full_name])){
			self::$counters[$full_name] = 1;
		}else{
			self::$counters[$full_name] += 1;
		}
		return self::$counters[$full_name];
	}
	
	/**
	 * 获取计数值。如果$caller为空则返回全部计数器数组；否则只返回指定数组。若指定caller不存在，则返回0。
	 * 
	 * @see Comm_Debug_CallerCounter::count()
	 * @param string|object|callback $caller 指定的caller。可选，如果忽略，采用null作为默认值。
	 * @return array|int 如果$caller为空，则返回所有计数器数组；否则返回一个整型计数结果。
	 */
	static public function get($caller = NULL){
		if(is_null($caller)){
			return self::$counters;
		}
		
		$full_name = self::get_func_full_name($caller);
		return isset(self::$counters[$full_name]) ? self::$counters[$full_name] : 0;
	}
	
	static protected function get_func_full_name_from_caller_info($caller_info){
		return isset($caller_info['type']) && $caller_info['type'] ? $caller_info['class'] . $caller_info['type'] . $caller_info['function'] : $caller_info['function'];
	}
	
	static protected function get_func_full_name($caller){
		if(is_array($caller)){
			if(is_object($caller[0])){
				$class = get_class($caller[0]);
				$type = '->';
			}else{
				$class = strval($caller[0]);
				$type = '::';
			}
			return $class . $type . strval($caller[1]);
		}
		return is_object($caller) ? get_class($caller) : strval($caller);
	}
}