<?php
class Sso_Sdk_Session_Memcache {

	const E_CONFIG_EMPTY        = 10200; //直接走接口验证
	const E_DELETED             = 10201; //返回失败
	const E_NOT_FOUND           = 10202; //走接口校验
	const E_NETWORK_EXCEPTION   = 10203; //只校验签名

	private static $_instance;
	private static $_reverse_cache = 'd'; //反向cache,这个可以定义在配置文件中的

	/** @var Sso_Sdk_Tools_Memcache  */
	private $_mc;
	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 私有构造方法
	 */
	private function __construct(){
		$arr_config = Sso_Sdk_Config::instance()->get("data.res.cache");
		if (!$arr_config || !is_array($arr_config) || count($arr_config) == 0) {
			throw new Exception('no config', self::E_CONFIG_EMPTY);
		}

		$this->_mc = new Sso_Sdk_Tools_Memcache();
		$this->_mc->addServers($arr_config);
		$this->_mc->setOption(Sso_Sdk_Tools_Memcache::OPT_CONNECT_RETRY_ON_TIMEOUT, Sso_Sdk_Config::get_user_config('mc_retry_times_on_connect_timeout'));
	}
	/**
	 * 使用cache校验sid的有效性
	 * @param Sso_Sdk_Session_Sid $sid
	 * @throws Exception
	 * @return Sso_Sdk_Session_Session
	 */
	public function validate(Sso_Sdk_Session_Sid $sid) {
		$key = $this->_create_key($sid);
		$timer_id = Sso_Sdk_Tools_Timer::start('mc get');
		try {
			$val = $this->_mc->getByKey($sid->get_uid(), $key);
			Sso_Sdk_Tools_Timer::stop($timer_id, (int)Sso_Sdk_Config::instance()->get('data.log.slowlog.mc.timeout'));

			$this->_mc->close();
		} catch (Exception $e) {
			Sso_Sdk_Tools_Timer::stop($timer_id, (int)Sso_Sdk_Config::instance()->get('data.log.slowlog.mc.timeout'));
			$this->_mc->close();
			Sso_Sdk_Tools_Debugger::warn($e->getMessage().'; errcode:'. $e->getCode(), 'mc get exception');
			switch($e->getCode()) {
				case Sso_Sdk_Tools_Memcache::E_CONFIG_NO_SERVERS:
					throw new Exception('No config', self::E_CONFIG_EMPTY);
				case Sso_Sdk_Tools_Memcache::E_RES_NOTFOUND:
					throw new Exception('No found', self::E_NOT_FOUND);
				default:
					throw new Exception('network exception:'. $e->getMessage(), self::E_NETWORK_EXCEPTION);
			}
		}
		if (!$val) {
			throw new Exception("No found", self::E_NOT_FOUND);  //外部需要判断处理该异常
		}
		$val = self::_remove_meta($val);
		if ($val == self::$_reverse_cache) {
			throw new Exception("deleted", self::E_DELETED); //刚刚被删除
		}
		$session = new Sso_Sdk_Session_Session($sid);
		$arr = Sso_Sdk_Session_Session::unserialize($val);
		$session->set_data($arr);

		return $session;
	}

	/**
	 * 生成cache存储key
	 * @param Sso_Sdk_Session_Sid $sid
	 * @return string
	 */
	private function _create_key(Sso_Sdk_Session_Sid $sid) {
		$sessionkey = Sso_Sdk_Session_Sid::create_session_key_by_sid($sid);
		return '{'.$sid->get_uid()."}_".$sessionkey;
	}
	/**
	 * 去掉session中的meta信息
	 * @param $value
	 * @return string
	 */
	private static function _remove_meta($value) {
		$value_version = ord($value{0});
		switch($value_version) {
			case 1:
				return substr($value, 5);
		}
		return $value;
	}
}