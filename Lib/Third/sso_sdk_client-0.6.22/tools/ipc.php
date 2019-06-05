<?php
/**
 * 进程内cache，避免重复的网络操作
 */

/**
 * Class Sso_Sdk_Tools_Ipc
 */
class Sso_Sdk_Tools_Ipc {

	private static $_storage = array();
	private static $_max    = array();   // 默认最多cache 10个元素
	private $_slab  = null;


	public function __construct($slab) {
		$this->_slab = $slab;
	}

	/**
	 * 设置cache大小
	 * @param $size
	 */
	public function set_cache_size($size) {
		self::$_max[$this->_slab] = $size;
	}
	
	/**
	 * 获取
	 * @param string $key  key值
	 * @return mixed
	 */
	public function get($key) {
		$ipc_disable = Sso_Sdk_Config::get_user_config('ipc_disable');
		if ($ipc_disable) return null;
		if (isset(self::$_storage[$this->_slab][$key]) && ( self::$_storage[$this->_slab][$key]['expire'] == 0 || self::$_storage[$this->_slab][$key]['expire'] >= time())) {
			return self::$_storage[$this->_slab][$key]['value'];
		}
		return null;
	}

	/**
	 * 设置
	 * @param string $key key值
	 * @param mixed $value value值
	 * @param int $expire 过期时间，相对时间，单位为s， 默认不过期
	 * @return bool
	 */
	public function set($key, $value, $expire = 0) {
		$ipc_disable = Sso_Sdk_Config::get_user_config('ipc_disable');
		if ($ipc_disable) return false;
		$max = isset(self::$_max[$this->_slab])?self::$_max[$this->_slab]:10;
		if (isset(self::$_storage[$this->_slab]) && count(self::$_storage[$this->_slab]) > $max) {
			array_shift(self::$_storage[$this->_slab]);
		}
		if($expire != 0) $expire = time() + $expire;
		self::$_storage[$this->_slab][$key] = array('value'=>$value, 'expire'=>$expire);
		return true;
	}

	/**
	 * 删除
	 * @param string $key  key值
	 * @return bool
	 */
	public function del($key){
		if(isset(self::$_storage[$this->_slab][$key])){
			unset(self::$_storage[$this->_slab][$key]);
			return true;
		}
		return false;
	}
	
	/**
	 * 清空缓存
	 * @return true
	 */
	public function clear() {
		self::$_storage[$this->_slab] = array();
		return true;
	}
	/**
	 * 清空缓存
	 * @return true
	 */
	public static  function clear_all() {
		self::$_storage = array();
		return true;
	}
}