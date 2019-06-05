<?php
/**
 * 调试工具
 */

/**
 * Class Sso_Sdk_Tools_Debugger
 */
class Sso_Sdk_Tools_Debugger {

	const DEBUG_LEVEL_INFO  = 1;
	const DEBUG_LEVEL_WARN  = 2;
	const DEBUG_LEVEL_ERROR = 4;
	const DEBUG_LEVEL_ALL   = -1;

	private static $_enable = false;
	private static $_level  = -1;
	private static $_shortfilename = false;

	/**
	 * 显示info级别信息
	 * @param string $msg
	 * @param string $label
	 */
	public static function info($msg, $label = '') {
		self::_write(self::DEBUG_LEVEL_INFO, $msg, $label);
	}

	/**
	 * 显示warn级别信息
	 * @param string $msg
	 * @param string $label
	 */
	public static function warn($msg, $label = '') {
		self::_write(self::DEBUG_LEVEL_WARN, $msg, $label);
	}

	/**
	 * 显示error级别信息
	 * @param string $msg
	 * @param string $label
	 */
	public static function error($msg, $label = '') {
		self::_write(self::DEBUG_LEVEL_ERROR, $msg, $label);
	}

	/**
	 * 开启调试
	 */
	public static function enable() {
		self::$_enable = true;
	}

	/**
	 * 检查调试是否开启
	 * @return bool
	 */
	public static function is_enable() {
		return self::$_enable;
	}
	/**
	 * 关闭调试
	 */
	public static function disable() {
		self::$_enable = false;
	}
	public static function set_level($level) {
		self::$_level = $level;
	}
	public static function set_shortfilename($on = true) {
		self::$_shortfilename = $on;
	}
	/**
	 * @param int $level
	 * @param $msg
	 * @param string $label
	 * @return bool
	 */
	private static function _write($level, $msg, $label) {
		if (self::$_enable !== true) return false;
		if (($level & self::$_level) !== $level) return false;

		if ($label) $label = $label . ':';
		if (PHP_SAPI != 'cli' && class_exists('FirePHP') && Sso_Sdk_Tools_Request::server('HTTP_X_FIREPHP_VERSION')) {
			self::_write_by_firephp($level, $msg, $label);
		} else {
			if (PHP_SAPI == 'cli') {
				self::_write_to_console($level, $msg, $label);
			} else {
				self::_write_to_webpage($level, $msg, $label);
			}
		}
		return true;
	}

	/**
	 * @param $level
	 * @param $msg
	 * @param $label
	 */
	private static function _write_by_firephp($level, $msg, $label) {
		/** @var FirePHP $firephp */
		$firephp = FirePHP::getInstance(true);
		$firephp->setOption('includeLineNumbers', false);
		$label = self::get_caller_info().':'.$label;
		switch($level) {
			case self::DEBUG_LEVEL_ERROR:
				$firephp->error($msg, $label);
				break;
			case self::DEBUG_LEVEL_WARN:
				$firephp->warn($msg, $label);
				break;
			case self::DEBUG_LEVEL_INFO:
				$firephp->info($msg, $label);
				break;
			default:
				$firephp->log($msg, $label);
		}
	}

	/**
	 * 打印到控制台
	 * @param $level
	 * @param $msg
	 * @param $label
	 */
	private static function _write_to_console($level, $msg, $label) {
		$PHP_EOL = "\n";
		$label = self::get_caller_info() . ':'. $label. $PHP_EOL;
		switch($level) {
			case self::DEBUG_LEVEL_ERROR:
				$label = "[ERROR] $label";
				break;
			case self::DEBUG_LEVEL_WARN:
				$label = "[WARN]  $label";
				break;
			case self::DEBUG_LEVEL_INFO:
				$label = "[INFO]  $label";
				break;
			default:
				$label = "[LOG]   $label";
		}
		echo $label;
		if (is_string($msg) || is_integer($msg)) {
			echo $msg, $PHP_EOL;
		} else {
			fputs(STDERR, var_export($msg, true) .$PHP_EOL); //这里信息量太大，写到标准错误，这样方便扔掉
		}

	}

	/**
	 * 直接打印到页面
	 * @param $level
	 * @param $msg
	 * @param $label
	 */
	private static function _write_to_webpage($level, $msg, $label) {
		$PHP_EOL = "<br/>";
		$style = '';
		switch($level) {
			case self::DEBUG_LEVEL_ERROR:
				$style = 'color:red';break;
			case self::DEBUG_LEVEL_WARN:
				$style = 'color:yellow';break;
		}
		echo "<div style='$style'>";
		echo self::get_caller_info() , $PHP_EOL, $label, $PHP_EOL;

		if (is_string($msg) || is_integer($msg)) {
			echo $msg, $PHP_EOL;
		} else {
			var_dump($msg);
		}
		echo '</div>';
	}

	/**
	 * 获取调用者信息
	 * @return string
	 */
	private static function get_caller_info() {
		$arr = debug_backtrace();
		$i = 3;
		$file   = $arr[$i]['file'];
		$line   = $arr[$i]['line'];
		$class  = $arr[$i+1]['class'];
		$method = $arr[$i+1]['function'];
		if(self::$_shortfilename === true) {
			$sdk_path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
			$file = str_replace($sdk_path, '', $file);
		}
		return $file.":$line:".$class.'->'.$method;
	}
}