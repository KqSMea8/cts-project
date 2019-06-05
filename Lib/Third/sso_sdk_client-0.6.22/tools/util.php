<?php
/**
 * 工具方法
 */

class Sso_Sdk_Tools_Util {

	/**
	 * 获取服务器端IP地址
	 * @return string
	 */
	public static function get_server_ip() {
		if (isset($_SERVER['SINASRV_INTIP'])) { // 动态平台环境变量
			return $_SERVER['SINASRV_INTIP'];
		} elseif (isset($_SERVER['SERVER_ADDR'])) {
			return $_SERVER['SERVER_ADDR'];
		}
		return php_uname('n'); //PHP 5.3 以后可以使用gethostname()，效果、效率都一样，这里为了兼容 < PHP 5.3的情况，所以使用了php_uname('n')
	}

	/**
	 * 获取请求服务器IP地址
	 * @return string
	 */
	public static function get_client_ip() {
		static $ip = null;
		if ($ip === null) {
			$ip = @$_SERVER['REMOTE_ADDR'];
			if ($ip && self::is_private_ip($ip)) {
				$xforward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
				if ($xforward) {
					$arr = explode(',', $xforward);
					$ip = trim(end($arr));
				}
			}
		}
		return $ip;
	}

	/**
	 * 判断是否私有ip（不包含测试ip和保留ip，如： 127.0.0.0/8, 169.254.0.0/16）
	 * @param string $ip
	 * @return bool
	 */
	public static function is_private_ip($ip) {
		$i = explode('.', $ip, 3);
		if ($i[0] == 10) return true;
		if ($i[0] == 172 && $i[1] > 15 && $i[1] < 32) return true;
		if ($i[0] == 192 && $i[1] == 168) return true;
		return false;
	}

	/**
	 * 通过点分的key从数组中查找信息
	 * @param $key string eg: data.res.cache
	 * @param $arr array  key can not contains dot
	 * @return array|null
	 */
	public static function get_key_in_array($key, $arr) {
		if (!is_array($arr)) return null;
		$arr_key = explode(".", $key);
		$tmp_arr = $arr;
		$cnt = count($arr_key);
		for($i = 0; $i < $cnt; $i++){
			if (!is_array($tmp_arr) || !isset($tmp_arr[$arr_key[$i]])) return null;
			$tmp_arr = $tmp_arr[$arr_key[$i]];
		}
		return $tmp_arr;
	}


	/**
	 * 递归的数组合并实现，用于配置的继承
	 * @param $array array
	 * @param $array1 array
	 * @return array
	 */
	public static function array_replace_recursive(array $array, array $array1) {
		if (function_exists('array_replace_recursive')) {
			return array_replace_recursive($array, $array1);
		}
		return self::_array_replace_recursive($array, $array1);
	}

	/**
	 * 递归的数组合并实现
	 * @param $array array
	 * @param $array1 array
	 * @return array
	 */
	private static function _array_replace_recursive(array $array, array $array1) {
		foreach ($array1 as $key => $value){
			// create new key in $array, if it is empty or not an array
			if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))){
				$array[$key] = array();
			}

			// overwrite the value in the base array
			if (is_array($value)){
				$value = self::_array_replace_recursive($array[$key], $value);
			}
			$array[$key] = $value;
		}
		return $array;
	}
	/**
	 * 遍历目录
	 * @param $dir
	 * @param string $suffix
	 * @return array
	 */
	public static function listdir($dir, $suffix = '') {
		$files = array();
		if (!is_dir($dir)) return $files;
		$handle = @opendir($dir);
		if (!$handle) return $files;
		while (($file = readdir($handle)) !== false) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			$filepath = $dir == '.' ? $file : $dir . '/' . $file;
			if (is_link($filepath)) {
				continue;
			}
			if (is_file($filepath)){
				if ($suffix == substr($filepath, -strlen($suffix), strlen($suffix))) {
					$files[] = $filepath;
				}
			}else if (is_dir($filepath)) {
				$files = array_merge($files, self::listdir($filepath, $suffix));
			}
		}
		closedir($handle);
		return $files;
	}

	/**
	 * 获取直接调用sdk的文件的路径和行号
	 * @return string
	 */
	public static function get_sdk_caller() {
		$arr_backtrace = debug_backtrace();
		$caller = '';
		foreach($arr_backtrace as $k=>$item) {
			if (isset($item['class']) && strpos($item['class'], "Sso_Sdk_") === 0 && $item['class'] !== "Sso_Sdk_Test") continue;
			$item = $arr_backtrace[$k-1];
			if (isset($item['file'], $item['line'])) {
				$caller = $item['file'].':'. $item['line'];
				break;  // 找离sdk最近的文件
			}
		}
		return $caller;
	}

	/**
	 * 获取当前请求的url
	 * @return string
	 */
	public static function get_request_url() {
		$scheme = Sso_Sdk_Tools_Request::is_https() ? 'https' : 'http';
		$host = Sso_Sdk_Tools_Request::server('HTTP_HOST');

		//由于 7 层做了内容转发，导致此处取到的 HTTP_HOST 可能与用户访问的地址不同，所以设置了一个修正机制
		if (($arr = Sso_Sdk_Config::get_user_config('host_mapping')) && isset($arr[$host])) {
			$host = $arr[$host];
		}

		if (!$host) return '';
		return $scheme . '://' . $host . Sso_Sdk_Tools_Request::server("REQUEST_URI");
	}

	/**
	 * 检查host是否domain的一个子域
	 * @param $host
	 * @param $domain
	 * @return bool
	 */
	public static function is_sub_domain($host, $domain) {
		if ($domain{0} == ".") $domain = substr($domain, 1);
		return (($pos = strpos($host, $domain)) !== false && ($pos+strlen($domain) == strlen($host)) && ($pos === 0 || $host{$pos-1} == "."));
	}

	/**
	 * 检测发现临时目录
	 */
	public static function get_temp_dir() {
		static $temp_dir = null;
		if ($temp_dir) return $temp_dir;
		$dir = '';
		if (isset($_SERVER['SINASRV_CACHE_DIR'])
			&& @is_dir($_SERVER['SINASRV_CACHE_DIR'])
			&& @is_writeable($_SERVER['SINASRV_CACHE_DIR'])) {
			$dir = $_SERVER['SINASRV_CACHE_DIR'];
		} else if(function_exists('sys_get_temp_dir')){
			$dir = @sys_get_temp_dir();
		}
		$dir = preg_replace('#/$#', '', $dir);
		if(!$dir || !@is_dir($dir)) {
			$dir = "/tmp";
		}
		$dir = $dir.DIRECTORY_SEPARATOR.'sso';
		if (!@is_dir($dir)) {
			if(!@mkdir($dir, 0777, true)) {
				throw new Exception("create dir $dir fail");
			}
		}
		$temp_dir = $dir;
		return $dir;
	}

	/**
	 * 创建临时文件
	 * @param $filename
	 * @throws Exception
	 * @return string
	 */
	public static function get_temp_file($filename) {
		$dir = self::get_temp_dir();
		$file = $dir.DIRECTORY_SEPARATOR.$filename;
		if (!@touch($file) || !@is_readable($file)) {
			throw new Exception("create temp file $file fail");
		}
		return $file;
	}

	/**
	 * 初始化可读写的文件
	 * @param $filename
	 * @return string
	 * @throws Exception
	 */
	public static function create_file($filename) {
		if (!@file_exists($filename)) {
			$dir = dirname($filename);
			if (!@file_exists($dir)) {
				if (!@mkdir($dir, 0777, true)) {
					throw new Exception("create dir $dir fail");
				}
			}
			if (!@touch($filename)) {
				throw new Exception("create file $filename fail");
			}
		} else if (!is_readable($filename) || !is_writable($filename)) {
			throw new Exception("file $filename is not readable or not writable");
		}
		return $filename;
	}
}
