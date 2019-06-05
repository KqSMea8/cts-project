<?php
/**
 * 本地存储
 */

class Sso_Sdk_Tools_LocalStorage_File implements Sso_Sdk_Tools_Localstorage_ILocalStorage{

	private static $_instance;
	private $_file = null;

	private function __construct() {
		$this->_file = $this->_get_file();
	}

	/**
	 * @return Sso_Sdk_Tools_LocalStorage_File
	 */
	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get(){
		return ($content = @file_get_contents($this->_file))?unserialize($content):null;
	}

	public function set($val){
		return @file_put_contents($this->_file, serialize($val), LOCK_EX);
	}
	public function delete() {
		return @unlink($this->_file);
	}
	public function get_storage_uri() {
		return $this->_file;
	}

	/**
	 * 可能存在的异常情况
	 * 1.定义的文件目录不存在
	 * 2.定义的文件无法创建
	 * 3.定义的文件不可写、或不可读
	 *
	 * 尽可能找到一个能用的，而不是抛异常
	 * @return null|string
	 * @throws Exception
	 */
	private function _get_file() {
		$file = Sso_Sdk_Config::get_user_config('config_cache_file');
		if (!$file) {
			return Sso_Sdk_Tools_Util::get_temp_file('sso_sdk_conf_cache_'. Sso_Sdk_Config::get_config_cache_suffix());
		}
		//添加个性后缀
		$file = $file.'_'. Sso_Sdk_Config::get_config_cache_suffix();
		return Sso_Sdk_Tools_Util::create_file($file);
	}
}

