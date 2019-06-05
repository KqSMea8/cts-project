<?php 

/**
 * web响应响应
 * @package Swift
 * @author Rodin <luodan@staff.sina.com.cn>
 */
class Comm_Response{
	static protected $use_jsonp_var = false;
	static protected $meta = array();
	
	/**
	 * 提供json顶级数据的定制
	 * 
	 * Tutorial:
	 * //默认:
	 * 	Comm_Response::out_json(0, 'okay', array('That is right'));
	 * 	//{'code' : 0, 'msg' : 'okay', 'data' : ['That is right']}
	 * //定制参数：
	 * 	Comm_Response::set_meta_data('key', 'foo');
	 * 	Comm_Response::out_json(0, 'okay', array('That is right'));
	 * 	//{'key': 'foo', 'code' : 0, 'msg' : 'okay', 'data' : ['That is right']}
	 * @param string $name
	 * @param mixed $value
	 */
	static public function set_meta_data($name, $value){
		self::$meta[$name] = $value;
	}
	
	/**
	 * 获取json顶级数据
	 * 
	 * @param string|null $name 数据名。可选，若空，则取null作为默认值。
	 * @return mixed 当$name为空时，返回全部meta数据，否则只返回指定的数据。若指定数据未设置过，则返回一个null
	 */
	static public function get_meta_data($name = null){
		return $name ? (isset(self::$meta[$name]) ? self::$meta[$name] : NULL) : self::$meta; 
	}
	
	/**
	 * 在输出jsonp的时候，将$callback参数作为变量处理。
	 * 
	 */
	static public function use_jsonp_as_var(){
		self::$use_jsonp_var = true;
	}
	
	/**
	 * 在输出jsonp的时候，将$callback参数作为变量处理
	 * 
	 */
	static public function use_jsonp_as_callback(){
		self::$use_jsonp_var = false;
	}
	
	/**
	 * 按json格式输出响应
	 * 
	 * @param string|int	$code			js的错误代码/行为代码
	 * @param string		$message		可选。行为所需文案或者错误详细描述。默认为空。
	 * @param mixed			$data			可选。附加数据。
	 * @param bool			$return_string	可选。是否返回一个字符串。默认情况将直接输出。
	 * @return string|void	取决与$return_string的设置。如果return_string为真，则返回渲染结果的字符串，否则直接输出，返回空
	 */
	static public function out_json($code, $message = '', $data = NULL, $return_string = false) {
		$json_string = json_encode(array_merge(self::$meta, array(
			"code" => $code,
			"msg" => strval($message),
			"data" => $data,
		)));
		if($return_string){
			return $json_string;
		}else{
			echo $json_string;
		}
	}
	
	/**
	 * 按jsonp格式输出响应
	 * 
	 * @param string		$callback		Javascript所需的回调函数名字。如果不合法，则会抛出一个异常。
	 * @param string		$code			Javascript所需的行为代码。
	 * @param string		$message		可选。行为所需文案或者错误详细描述。默认为空。
	 * @param mixed			$data			可选。附加数据。
	 * @param bool			$return_string	可选。是否返回一个字符串。默认情况将直接输出。
	 * @return string|void	取决于$return_string的设置。如果return_string为真，则返回渲染结果的字符串，否则直接输出，返回空
	 * 
	 * @throws Comm_Exception_Program
	 */
	static public function out_jsonp($callback, $code, $message = '', $data = NULL, $return_string = false){
		if(preg_match('/^[\w\$\.]+$/iD', $callback)){
			$jsonp = (!self::$use_jsonp_var ? "window.{$callback} && {$callback}(" : "var {$callback}=") . self::out_json($code, $message, $data, true) . (!self::$use_jsonp_var ? ")" : "") . ';';
			if($return_string){
				return $jsonp; 
			}else{
				echo $jsonp;
				return ;
			}
		}
		throw new Comm_Exception_Program('callback name invalid');
	}
	
	/**
	 * 输出需要用iframe嵌套的jsonp
	 * 
	 * @param string		$callback		Javascript所需的回调函数名字。如果不合法，则会抛出一个异常。
	 * @param string		$code			Javascript所需的行为代码。
	 * @param string		$message		可选。行为所需文案或者错误详细描述。默认为空。
	 * @param mixed			$data			可选。附加数据。
	 * @see out_jsonp
	 */
	static public function out_jsonp_iframe($callback, $code, $message = '', $data = NULL){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">document.domain="weibo.com";'
			. self::out_jsonp($callback, $code, $message, $data, true)
			. '</script>';
	}
	
	/**
	 * 直接输出内容
	 * 
	 * @param string $text
	 */
	static public function out_plain($text){
		echo $text;
	}
}