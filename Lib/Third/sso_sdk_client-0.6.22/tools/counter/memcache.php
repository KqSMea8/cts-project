<?php
/**
 * 基于memcache的计数器
 * Class Sso_Sdk_Tools_Counter_Memcache
 */
class Sso_Sdk_Tools_Counter_Memcache implements Sso_Sdk_Tools_Counter_ICounter{
	/** @var Sso_Sdk_Tools_Counter_Memcache  */
	private static $_instance;
	/** @var Sso_Sdk_Tools_Memcache */
	private $_mc;

	/**
	 * 单实例方法
	 * @return Sso_Sdk_Tools_Counter_Memcache
	 */
	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 私有构造函数
	 */
	private function __construct() {
		$arr_config = Sso_Sdk_Config::get_user_config('counter.memcache.servers');
		if (!is_array($arr_config)) {
			throw new Exception('no config (counter.memcache.servers) yet');
		}
		$arr = array();
		foreach($arr_config as $item) {
			if (!isset($item['host']) || !isset($item['port'])) continue;
			$item['attr'] = array('status' => 'ok');
			$arr[] = $item;
		}
		if (count($arr) === 0) {
			throw new Exception('no config yet');
		}
		$this->_mc = new Sso_Sdk_Tools_Memcache();
		$this->_mc->addServers($arr);
	}

	/**
	 * 当计数超过最大值时，设置过期时间
	 * @param $type string 计数器ID
	 * @param $val int 一般为最大值
	 * @param $expire int 过期时间
	 * @return mixed
	 */
	public function set($type, $val, $expire) {
		$key = $this->_get_key($type);
		return $this->_mc->set($key, $val, $expire);
	}

	/**
	 * 增加一个指定类型的计数
	 * @param $type string 计数器ID
	 * @param $expire int 过期时间
	 * @return int
	 */
	public function incr($type, $expire) {
		$key = $this->_get_key($type);
		return $this->_mc->increment($key, 1, 0, $expire); //increment不会修改一个已存在的对象的过期时间
	}

	/**
	 * 获取指定类型的计数
	 * @param $type string 计数器ID
	 * @return int
	 */
	public function get($type) {
		$key = $this->_get_key($type);
		try{
			return (int)$this->_mc->get($key);
		} catch (Exception $e){
			//这里不处理异常
			return 0;
		}
	}

	/**
	 * 重置指定的计数器
	 * @param $type string 计数器ID
	 * @return int
	 */
	public function reset($type) {
		$key = $this->_get_key($type);
		$this->_mc->delete($key);
		return 0;
	}

	/**
	 * 返回计数器存储位置的序列化表示
	 * @return string
	 */
	public function get_storage_uri() {
		return @json_encode(Sso_Sdk_Config::get_user_config('counter.memcache.servers'));
	}

	/**
	 * 获取一个计数器对应的存储key
	 * @param $type
	 * @return string
	 */
	private function _get_key($type) {
		return Sso_Sdk_Config::get_user_config('counter.memcache.key_prefix').'_'. Sso_Sdk_Config::get_config_cache_suffix().$type;
	}
}