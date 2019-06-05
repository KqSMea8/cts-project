<?php 

/**
 * 安全相关的一些常用方法
 * 
 * @author Rodin <luodan@staff.sina.com.cn>
 */
class Comm_Security {
	static protected $trusted_domains = array();
	static protected $untrusted_domains = array();
	static protected $salt = 'A random string which should be *long* enough and -complex- enough to make forms safer';
	
	/**
	 * 摘要算法
	 * 
	 * 被用于生成protect_csrf_form的校验字串
	 * 
	 * @var callback
	 */
	static public $digest_method = 'crc32';
	
	/**
	 * 添加信任域
	 * 
	 * @see Comm_Security::check_url_is_trusted
	 * @param string $domain 需要信任的域
	 * @param bool $trust_subdomain 是否也信任该域的子域。可选，默认为真。
	 */
	static public function add_trusted_domain($domain, $trust_subdomain = true){
		self::$trusted_domains[$domain] = $trust_subdomain;
	}
	
	/**
	 * 添加不信任域
	 * 
	 * @see Comm_Security::check_url_is_trusted
	 * @param string $domain 需要不信任的域
	 * @param bool $trust_subdomain 是否也不信任该域的子域。可选，默认为真。
	 */
	static public function add_untrusted_domain($domain, $include_subdomain = true){
		self::$untrusted_domains[$domain] = $include_subdomain;
	}
	
	/**
	 * 判断一个url的域是否可被信任
	 * 
	 * 信任域通过Comm_Security::add_trusted_domain添加
	 * 
	 * @see Comm_Security::add_trusted_domain
	 * @param string $url 
	 * @return bool 是否被信任
	 */
	static public function check_url_is_trusted($url){
		$host = @parse_url($url, PHP_URL_HOST);
		
		if(!$host){
			return false;
		}
		
		foreach (self::$untrusted_domains as $domain => $include_subdomain){
			if($host === $domain){
				return false;
			}
			if($include_subdomain && self::is_subdomain_of($host, $domain)){
				return false;
			}
		}
		
		foreach (self::$trusted_domains as $domain => $trust_subdomain){
			if($host === $domain){
				return true;
			}
			if($trust_subdomain && self::is_subdomain_of($host, $domain)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 检查指定url的get参数值里是否包含有可能造成xss攻击的字符串。
	 * 
	 * 主要用于Referer地址的安全判断。
	 * 可以添加多个额外的字符串（需给出不带边界符的正则格式）一并检查。
	 * 
	 * @param string $url	给定的url
	 * @param array $additional_strings 额外的需要进行检查的危险字符。
	 * @return bool
	 */
	static public function check_insecure_string_in_url_params($url, array $additional_strings = NULL){
		$query = @parse_url($url, PHP_URL_QUERY);
		if(!$query){
			return true;
		}
		
		@parse_str($query, $params);
		foreach ($params as $name => $value){
			if(!self::check_insecure_string($value, $additional_strings)){
				return false;
			}
		}
		return true;
	}
    
	
	/**
	 * 检查是否包含有可能引起XSS的危险字符
	 * 
	 * @param string $string
	 * @param array $additional_strings
	 * @return bool
	 */
    public static function check_insecure_string($string, array $additional_strings = NULL) {
    	
    	$insecure_patterns = array(
	    	/*document xss中常用到的js对象 */'(document.)+',  
			/*Element dom调用的关键字*/ '(.)?([a-zA-Z]+)?(Element)+(.*)?(\()+(.)*(\))+',  
			/*script 脚本标签关键字*/ '(<script)+[\s]?(.)*(>)+',
			/*src 外调源地址属性*/ 'src[\s]?(=)+(.)*(>)+',  
			/*on**(事件) 一些标签事件,比如onload等*/ '[\s]+on[a-zA-Z]+[\s]?(=)+(.)*',  
			/*XMLHttp ajax提交请求关键字*/ 'new[\s]+XMLHttp[a-zA-Z]+', 
			/*import 外部css调用*/ '\@import[\s]+(\")?(\')?(http\:\/\/)?(url)?(\()?(javascript:)?',
    	);
    	
        if ($additional_strings !== null) {
            $insecure_patterns += $additional_strings;
        }
        
        foreach ($insecure_patterns as $pattern){
        	if(preg_match('/' . $pattern . '/i', $string)){
        		return false;
        	}
        }
        
        return true;
    }
	
	/**
	 * 对html表单进行csrf保护。可生成一个不可预料的值存放在隐藏的input框。
	 * 
	 * Tutorial:
	 * <pre>
	 * //html:
	 * <form action="submit.php" method="post">
	 * <?php echo Comm_Security::protect_csrf_form('csrf_protecter')?>
	 * <input type="text" name="foo" value="bar"/>
	 * </form>
	 * 
	 * //submit.php
	 * <?php
	 * if(!Comm_Security::validate_csrf_value($_POST['csrf_protecter'])){
	 * 		die('Cracker!');
	 * }
	 * </pre>
	 * 
	 * @param string $challenge_input_name 表单的input名。可选，默认为空。若为空，则返回生成的挑战值。
	 * @return 表单input的html。如果$challenge_input_name为空，则返回生成的挑战值。
	 */
	static public function protect_csrf_form($challenge_input_name = ''){
		$timestamp = sprintf('%x', time());
		$random = str_pad(sprintf('%x', rand(0, 0x7FFFFFFF)), 8, '0', STR_PAD_LEFT);
		$challenge = strtolower(call_user_func(self::$digest_method, $random . self::$salt . $timestamp));
		$key = $random . $challenge . $timestamp;
		
		if(!$challenge_input_name){//key only
			return $key;
		}
		return '<input type="hidden" name="' . $challenge_input_name . '" value="' . $key . '"/>';
	}
	
	/**
	 * Comm_Security::protect_csrf_form 的smarty function插件版。可以使用smarty::register_function
	 * 
	 * @param array $params 只接受一个参数name。如果未提供name，则会使用swift_csrf_challenge作为默认name
	 * @param Smarty $smarty
	 * @return html string
	 */
	static public function protect_csrf_form_smarty_function($params, $smarty){
		if (!isset($params['name']) || !$params['name']){
			$name = 'swift_csrf_challenge';
		}else{
			$name = $params['name'];
		}
		
		return self::protect_csrf_form($name);
	}
	
	/**
	 * 验证csrf挑战值
	 * @param string $challenge_value 挑战值。传入值而非键名！
	 * @return bool 是否通过验证。
	 */
	static public function validate_csrf_value($challenge_value){
		$length = strlen($challenge_value);
		//use less than to make the false digested value compatible.
		if($length < 16){
			return false;
		}
		$random = substr($challenge_value, 0, 8);
		$challenge = substr($challenge_value, 8, $length - 16);
		$timestamp = substr($challenge_value, -8);
		
		return $challenge === strtolower(call_user_func(self::$digest_method, $random . self::$salt . $timestamp));
	}
	
	static protected function is_subdomain_of($subdomain, $domain){
		if(strlen($domain) >= strlen($subdomain)){
			return false;
		}
		
		if(substr($subdomain, -strlen($domain) - 1) === '.' . $domain){
			return true;
		}
		return false;
	}
}