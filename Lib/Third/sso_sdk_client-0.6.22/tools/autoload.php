<?php

class Sso_Sdk_Autoload {

	public static function init() {
		if(function_exists('spl_autoload_register')) {
			self::_autoload_use_spl();
		} else {
			self::_autoload_no_spl();
		}
	}

	/**
	 * autoload 逻辑
	 * 注意：
	 * 1. 避免先注册的autoload出问题，把sdk的autoload写在最前面；两个办法：
	 *  1.1 对于 php 5.3.0 之前的版本，不支持prepend参数，这里先卸载已注册的autoload，然后注册sdk的autoload，最后再把卸载下来的autoload注册上去
	 *  1.2 对于 php 5.3.0 之后的版本，直接通过prepend参数实现
	 *  1.3 这里的autoload方法因为是优先调用的，而且只负责本sdk内部的autoload
	 *      如果有其他框架的autoload发生在了本sdk的autoload之前，则框架的autoload找不到欲加载的类时，不能抛异常，否则同样出错
	 *  1.4 一般实现autoload时都不会在找不到要加载的类时在autoload中报错误，所以， 优先注册sdk的autoload的意义不是太大
	 */
	private static function _autoload_use_spl() {
		$sdk_autoload = array(__CLASS__, 'autoload');
		// 注册sdk外部的autoload
		if (function_exists('__autoload')) {
			spl_autoload_register('__autoload');
		}

		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			spl_autoload_register($sdk_autoload, true, true); //因为这次注册的autoload是肯定不会出错的，所以第二个参数是啥都没关系
		} else {
			$arr = spl_autoload_functions();
			if (!$arr) $arr = array();
			// 卸载已有autoload
			foreach($arr as $autoload) {
				spl_autoload_unregister($autoload);
			}
			// 先注册sdk的autoload
			spl_autoload_register($sdk_autoload);
			foreach($arr as $autoload) {
				spl_autoload_register($autoload);
			}
		}
	}

	/**
	 * autoload 方法 （原本想写个匿名函数，苦于PHP 5.3一下不支持，所以写了个方法）
	 * autoload 方法最好是public的，因为其他的框架做autoload时，可能会卸载所有已注册的autoload方法，然后重新注册回去，如果是非public
	 * 的方法，卸载后重新注册回去是不能用的
	 * @param $classname
	 */
	public static function autoload($classname){
		$sdk_root = dirname(dirname(__FILE__));
		$classname = strtolower($classname);
		if (substr($classname, 0, 8) === 'sso_sdk_'){
			$path = str_replace('_', DIRECTORY_SEPARATOR, substr($classname, 8));
			include_once($sdk_root . DIRECTORY_SEPARATOR . "$path.php");
		}
	}

	/**
	 * 载入所有文件
	 */
	private static function _autoload_no_spl() {
		//即使没有定义__autoload,这里也不能定义__autoload,因为使用方可能稍后就会定义__autoload
		//由于类之间有依赖关系，所以需要谨慎处理文件的加载顺序
		$sdk_root = dirname(dirname(__FILE__));
		require_once($sdk_root . "/tools/counter/icounter.php");
		require_once($sdk_root . "/tools/localstorage/ilocalstorage.php");
		require_once($sdk_root . "/tools/logger/ilogger.php");
		require_once($sdk_root . "/tools/util.php");
		foreach(Sso_Sdk_Tools_Util::listdir($sdk_root, '.php') as $file) {
			if ($file != ($sdk_root .DIRECTORY_SEPARATOR.'test.php')){
				/** @noinspection PhpIncludeInspection */
				require_once($file);
			}
		}
	}
}