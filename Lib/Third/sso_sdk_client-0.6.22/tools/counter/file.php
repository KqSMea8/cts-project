<?php
/**
 * 基于文件的计数器
 * /dev/shm会不会比/tmp更好一些？
 * Class Sso_Sdk_Tools_Counter_File
 */
class Sso_Sdk_Tools_Counter_File implements Sso_Sdk_Tools_Counter_ICounter{
	private static $_instance = null;
	private $_file = null;

	private function __construct() {
		$this->_file = $this->_init_file();
	}

	/**
	 * 初始化计数器文件
	 * @return null|string
	 * @throws Exception
	 */
	private function _init_file() {
		$filename = 'sso_sdk_counter_file_'. Sso_Sdk_Config::get_config_cache_suffix();
		$dir = Sso_Sdk_Config::get_user_config('counter.file.path');

		if (!$dir) {
			return Sso_Sdk_Tools_Util::get_temp_file($filename);
		}
		//添加个性后缀
		$filename = $dir.DIRECTORY_SEPARATOR.$filename;
		return Sso_Sdk_Tools_Util::create_file($filename);
	}
	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 当计数超过最大值时，设置过期时间
	 * @param $type string 计数器ID
	 * @param $val int 一般为最大值
	 * @param $expire int 过期时间
	 * @return mixed
	 */
	public function set($type, $val, $expire) {
		if ($expire < 30*86400) $expire = time() + $expire;
		// 出于特殊情况下性能的问题，不对文件进行加锁，少计数几次也没太大关系
		$arr_content = $this->_read();
		$arr_content[$type] = array('value'=> $val, 'expire'=> $expire);
		$this->_write($arr_content);
		return $arr_content[$type]['value'];
	}

	/**
	 * 增加一个指定类型的计数，不修改过期时间,最大相对时间1个月，超过1个月视为绝对时间
	 * @param $type string 计数器ID
	 * @param $expire int 过期时间
	 * @return int
	 */
	public function incr($type, $expire) {
		if ($expire < 30*86400) $expire = time() + $expire;
		// 出于特殊情况下性能的问题，不对文件进行加锁，少计数几次也没太大关系
		$arr_content = $this->_read();
		if (isset($arr_content[$type]) && isset($arr_content[$type]['value']) && isset($arr_content[$type]['expire'])
			&& $arr_content[$type]['expire'] > time()) { // 存在并且未过期
			$arr_content[$type]['value']++;
		} else {
			$arr_content[$type] = array('value'=> 1, 'expire'=> $expire);
		}
		$this->_write($arr_content);
		return $arr_content[$type]['value'];
	}

	/**
	 * 获取指定类型的计数
	 * @param $type string 计数器ID
	 * @return int
	 */
	public function get($type) {
		$arr_content = $this->_read();
		return (isset($arr_content[$type]) && isset($arr_content[$type]['value'])  && isset($arr_content[$type]['expire']) && $arr_content[$type]['expire'] > time())?$arr_content[$type]['value']:0;
	}

	/**
	 * 重置指定的计数器
	 * @param $type string 计数器ID
	 * @return int
	 */
	public function reset($type) {
		$arr_content = $this->_read();
		unset($arr_content[$type]);
		$this->_write($arr_content);
		return 0;
	}

	/**
	 * 返回计数器存储位置的序列化表示
	 * @return string
	 */
	public function get_storage_uri() {
		return $this->_file;
	}

	/**
	 * 读取文件中所有计数器信息
	 * @return array
	 */
	private function _read() {
		$ipc = new Sso_Sdk_Tools_Ipc('counter_file');
		$arr_content = $ipc->get('content');
		if ($arr_content !== null) return $arr_content;

		$arr_content = unserialize(@file_get_contents($this->_file));
		if (!is_array($arr_content)) {
			$arr_content = array();
		}
		$ipc->set('content', $arr_content);
		return $arr_content;
	}

	/**
	 * 将计数器信息写入文件
	 * @param array $arr_content
	 * @return bool
	 */
	private function _write(array $arr_content) {
		$content = serialize($arr_content);
		if(! @file_put_contents($this->_file, $content)) {
			Sso_Sdk_Tools_Log::instance()->error('counter', $this->get_storage_uri().':'. $content);
			return false;
		}
		$ipc = new Sso_Sdk_Tools_Ipc('counter_file');
		$ipc->del('content');
		return true;
	}
}