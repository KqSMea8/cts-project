<?php
/**
 * 负责解析提供配置信息
 */

class Sso_Sdk_Config {

	private static $_config_url = "http://i.conf.sso.sina.com.cn/api/session/config";
	private $_arr_conf = array();
	private $_arr_conf_default = array(
		'data' => array(
			'main'      => array(
				'renew_sid' => array(
					'after_ctime'   => 86400,   //创建时间在1天前，则需要更新
					'before_etime'  => 0,       //距离过期时间在0s内，则需要更新
				),
				'sub'       => array(
					'no_remote_validate_status'	 => array(      //某些状态的sub是不需要远程校验的
						Sso_Sdk_Session_Session::STATUS_VISITOR,
						Sso_Sdk_Session_Session::STATUS_EXITED,
						Sso_Sdk_Session_Session::STATUS_EXPIRED
					),
				),
			),
			'timeout'   => array( //关于超时时间的默认配置
				'mc'  => array(
					'connect_timeout'   => 1000, //ms
					'send_timeout'   => 1000, //ms
					'recv_timeout'   => 1000, //ms
					'poll_timeout'   => 1000, //ms
				),
				'http'  => array(
					'timeout'   => 3000, //ms
				),
				'dns'  => array(
					'timeout'   => 1000, //ms
				),
			),
			'counter'   => array( //关于计数器的默认配置
				Sso_Sdk_Tools_Counter::TYPE_HTTP_VALIDATE_ERROR => array(
					'period'    => 300,  //计数周期，单位：秒
					'max'       => 20,   //一个计数周期内允许的最大次数
					'duration'  => 600, //超过最大次数后禁用多长时间
				),
				Sso_Sdk_Tools_Counter::TYPE_HTTP_DESTROY_ERROR  => array(
					'period'    => 300,  //计数周期，单位：秒
					'max'       => 20,   //一个计数周期内允许的最大次数
					'duration'  => 600, //超过最大次数后禁用多长时间
				),
				Sso_Sdk_Tools_Counter::TYPE_MEMCACHE_ERROR => array(
					'period'    => 300,  //计数周期，单位：秒
					'max'       => 20,   //一个计数周期内允许的最大次数
					'duration'  => 600, //超过最大次数后禁用多长时间
				),
				Sso_Sdk_Tools_Counter::TYPE_DNS_LOOKUP_ERROR => array(
					'period'    => 300,  //计数周期，单位：秒
					'max'       => 20,   //一个计数周期内允许的最大次数
					'duration'  => 600, //超过最大次数后禁用多长时间
				),
				Sso_Sdk_Tools_Counter::TYPE_DNS_LOOKUP_TIMEOUT => array(
					'period'    => 300,  //计数周期，单位：秒
					'max'       => 20,   //一个计数周期内允许的最大次数
					'duration'  => 600, //超过最大次数后禁用多长时间
				),
				Sso_Sdk_Tools_Counter::TYPE_HTTP_SUS_VALIDATE_ERROR => array(
					'period'    => 300,  //计数周期，单位：秒
					'max'       => 20,   //一个计数周期内允许的最大次数
					'duration'  => 600, //超过最大次数后禁用多长时间
				),
				Sso_Sdk_Tools_Counter::TYPE_HTTP_LOCK_QUERY_ERROR => array(
					'period'    => 300,  //计数周期，单位：秒
					'max'       => 20,   //一个计数周期内允许的最大次数
					'duration'  => 600, //超过最大次数后禁用多长时间
				),
			)
		),
	);

	/** @var Sso_Sdk_Tools_Localstorage_ILocalStorage  */
	private static $_localstorage;

	/** @var  Sso_Sdk_Tools_Counter_ICounter */
	private static $_counter;
	/** @var  Sso_Sdk_Tools_Logger_ILogger */
	private static $_logger;

	private static $_instance;
	private static $_arr_user_config = array(
		'entry'         => '',      //通行证统一颁发的产品标识
		'service'       => '',      //一般与entry相同
		'pin'           => '',          //与entry配对的秘钥
		'sudaref'       =>'',		//微博主站统计用户进入微博方式所需要的参数
		'domain'        => '.sina.com.cn',  //产品所在根域
		'idc'           => null,        //当前IDC标识
		'check_level'   => -1,           // deprecated 校验级别；默认：check all
		'check_domain'  => true,        // 替代" check_level " 校验cookie时，是否做域的校验，默认为true
		'remote_validate'   => true,    //是否开启服务器端校验，默认开启
		'allow_renew'       => false,   // 是否允许renew sid，默认不允许
		'config_cache_file' => null,    //config缓存文件路径
		'useticket'         => false,   //是否使用ticket，非.sina.com.cn域一般都应设置为true
		'returntype'        => 'META',   //自动登录时的返回方式
		'host_mapping'      => array(), //对于七层转发时修改了HTTP_HOST的情况，这里需要配置
		'autologin_query'   => array(), //自动登录时期望附加给登录地址的自定义参数
		'validatesid_query' => array(   //校验sid时需要的参数
			'dinfo' => null, //设备信息
			'aid'	=> null, // 指纹系统设备ID
		),
		'proj'              => null,    //用于校验gsid，只有确认会校验gsid时才需要该设置
		'ivalue'            => null,    //用于校验gsid，只有确认会校验gsid时才需要该设置
		'ipc_disable'       => false,         //允许关闭进程内cache
		'counter'           => array(           //计数器配置
			/*
			'file' => array(
				'path'=> '/tmp/'        // 其实动态平台的该目录不给写
			),
			'memcache' => array(
				'servers' => array(
					array( 'host'  => null, 'port'  => null),
				),
				'key_prefix' => 'sso_sdk_counter'
			)
			*/
		),
		'proxy' => array(               //域名做key，而且域名中的dot用下划线代替，如： i_session_sso_sina_com_cn
			'hostname_replace_dot_by_underline' => array('host'=>null, 'port'=>null),
		),
		'autosetrenewcookie'    => true,   //是否自动设置已更新的cookie
		'autosetlogoutcookie'   => true,   //退出时是否自动删除cookie
		'autologin'             => true,   //是否允许自动登录，极端情况下才disable该选项
		'timeout'               => null,   //关于超时时间的配置
		'custom_verify_flag'    => 0,       //当需要验证某些信息时，这里的标志位是被使用方处理的
		'ignore_verify_flag'    => array(),       //用于替代 "custom_verify_flag", 当需要验证某些信息时，这里的标志位是被使用方处理的,可选值： need_vsn/need_pincode/need_change_pwd/readonly/all
		'auto_create_visitor'   => false,  //是否自动创建访客账号，默认不自动创建
		'mc_retry_times_on_connect_timeout'   => 0,  //校验session连接mc超时时，重试连接的次数，默认不重试
		'authinfo'              => array(   //认证信息
			'ticket'    => null,
			'sub'       => null,
			'gsid'      => null,
		),
		'authinfo_ignore'       => array(), //可用值有sub/gsid/ticket
		'need_weiboinfo' => array(),  // 是否在获取用户信息（调用get_userinfo_by_uid(..)）时返回微博信息
		'scf_verify_flag' => false,    // 是否验证scf(可信设备)，某些安全级别高的情况下启用，例如微博支付
		'need_safe_device' => true, //是否支持设备锁，默认支持
        'sub_signs' => array(),
	);


	private function __construct() {
		$arr_conf = $this->_init_conf();
		if ($arr_conf && is_array($arr_conf)) {
			//允许覆盖部分系统配置
			if (isset(self::$_arr_user_config['timeout']) && isset($arr_conf['data']['timeout']) && is_array($arr_conf['data']['timeout'])) {
				$arr_conf['data']['timeout'] = Sso_Sdk_Tools_Util::array_replace_recursive($arr_conf['data']['timeout'], self::$_arr_user_config['timeout']);
			}
			$this->_arr_conf = $arr_conf;
			if (Sso_Sdk_Tools_Debugger::is_enable()) { //仅仅出于性能考虑，对于线上情况只会做一次判断，先判断一次比判断两次好那么一点点
				Sso_Sdk_Tools_Debugger::info($arr_conf, 'sys config');
				Sso_Sdk_Tools_Debugger::info(self::$_arr_user_config, 'user config');
			}
		}
	}
	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 允许自定义本地存储
	 * @param Sso_Sdk_Tools_Localstorage_ILocalStorage $localstorage
	 */
	public static function set_localstorage(Sso_Sdk_Tools_Localstorage_ILocalStorage $localstorage) {
		self::$_localstorage = $localstorage;
	}

	/**
	 * 删除自定义的localstorage
	 */
	public static function unset_localstorage() {
		self::$_localstorage = null;
	}
	/**
	 * 返回可用的本地存储(测试时会用到)
	 * @return Sso_Sdk_Tools_Localstorage_ILocalStorage
	 */
	public static function get_localstorage() {
		return self::_get_localstorage();
	}

	/**
	 * 返回可用的本地存储
	 * @return Sso_Sdk_Tools_Localstorage_ILocalStorage
	 */
	private static function _get_localstorage() {
		return self::$_localstorage?self::$_localstorage:Sso_Sdk_Tools_LocalStorage_File::instance();
	}

	/**
	 * 允许自定义计数器类
	 * @param Sso_Sdk_Tools_Counter_ICounter $counter
	 */
	public static function set_counter(Sso_Sdk_Tools_Counter_ICounter $counter) {
		self::$_counter = $counter;
	}

	/**
	 * 删除自定义的counter
	 */
	public static function unset_counter() {
		self::$_counter = null;
	}

	/**
	 * 返回可用的计数器
	 * @return Sso_Sdk_Tools_Counter_ICounter
	 */
	public static function get_counter() {
		if (self::$_counter) return self::$_counter;
		if (Sso_Sdk_Config::instance()->get('data.counter.memcache.servers')) {
			try{
				return Sso_Sdk_Tools_Counter_Memcache::instance();
			} catch (Exception $e){
			}
		}
		return Sso_Sdk_Tools_Counter_File::instance();  //如果文件计数器都没有了就得抛异常了
	}

	/**
	 * 设置一个自定义的记录日志的对象（一般用于调试）
	 * @param Sso_Sdk_Tools_Logger_ILogger $logger
	 */
	public static function set_logger(Sso_Sdk_Tools_Logger_ILogger $logger) {
		self::$_logger = $logger;
	}

	/**
	 * 删除自定义的logger
	 */
	public static function unset_logger() {
		self::$_logger = null;
	}
	/**
	 * 获取自定义的记录日志的对象
	 * @return Sso_Sdk_Tools_Logger_ILogger
	 */
	public static function get_logger() {
		return self::$_logger;
	}
	/**
	 * 需要使用方自己做的配置
	 * @param array $arr
	 */
	public static function set_user_config(array $arr){
		self::$_arr_user_config = Sso_Sdk_Tools_Util::array_replace_recursive(self::$_arr_user_config, $arr);
	}

	/**
	 * 设置自定义的认证信息，可用值为：sub、gsid、ticket
	 * @param $key
	 * @param $val
	 */
	public static function set_authinfo($key, $val) {
		self::$_arr_user_config['authinfo'][strtolower($key)] = $val;
	}

	/**
	 * 忽略指定的认证信息，可用值为：sub、gsid、ticket
	 * @param $key
	 */
	public static function ignore_authinfo($key) {
		self::$_arr_user_config['authinfo_ignore'][strtolower($key)] = 1;
	}

	/**
	 * 获取应用配置信息
	 * @param $key
	 * @return null
	 */
	public static function get_user_config($key) {
		if (isset(self::$_arr_user_config[$key])) return self::$_arr_user_config[$key]; //此行为根据实际情况的优化
		return Sso_Sdk_Tools_Util::get_key_in_array($key, self::$_arr_user_config);
	}

	/**
	 * 建议的cache唯一标识，实现配置文件cache类时，需要用到该方法
	 * @return string
	 */
	public static function get_config_cache_suffix(){
		$sdk = 'sso_client';
		$version = Sso_Sdk_Client::get_version();
		$entry = Sso_Sdk_Config::get_user_config('entry');
		$domain = Sso_Sdk_Config::get_user_config('domain');
		$idc = Sso_Sdk_Config::get_user_config('idc');
		$ip = Sso_Sdk_Tools_Util::get_server_ip();
		return "{$sdk}_{$version}_{$entry}_{$domain}_{$idc}_{$ip}";
	}

	/**
	 * 通过点分多级的方式访问指定位置的信息
	 * @param $key
	 * @return array|null
	 */
	public function get($key) {
		static $cache = array();
		if (!$cache && $this->_arr_conf) {
			$cache = Sso_Sdk_Tools_Util::array_replace_recursive($this->_arr_conf_default, $this->_arr_conf);
		}
		if ($cache){
			$arr_conf = Sso_Sdk_Tools_Util::get_key_in_array($key, $cache);
		} else {
			$arr_conf = Sso_Sdk_Tools_Util::get_key_in_array($key, $this->_arr_conf_default);
		}
		return $arr_conf;
	}

	/**
	 * only for test
	 * @param $etag
	 * @return mixed
	 */
	public function test_304($etag) {
		return $this->_get_conf_by_http($etag);
	}

	/**
	 * 初始化配置信息
	 */
	private function _init_conf() {
		$should_update_cache = false;
		$localstorage = self::_get_localstorage();
		$cache = $localstorage->get();
		if (!is_array($cache) || !$this->_is_valid_conf($cache)) {
			$cache = null;
			$localstorage->delete();
		}
		static $http_in_use = 0;    //避免循环调用
		if ($http_in_use) return $cache;

		$arr_http_result = null;
		if (!$cache) {          // cache 不存在
			$http_in_use = 1;
			try{
				$arr_http_result = $this->_get_conf_by_http();
			} catch (Exception $e){
				throw $e;   //该异常必须处理
			}
		} elseif ($cache['meta']['expire'] < time()) {  // cache 已过期
			if (time() - $cache['meta']['expire'] > 300 // 说明访问量比较少，可以直接更新
				|| rand(0, 500) == 0        // 如果访问量大，允许 1/500 的请求发起更新操作
			) {
				$http_in_use = 1;
				try{
					$arr_http_result = $this->_get_conf_by_http($cache['meta']['etag']);
				} catch (Exception $e){ //该异常可以不影响正常逻辑，下层已有报警日志，这里不处理该异常
				}
			} else {
				return $cache;
			}
		} else {
			return $cache;
		}

		if (!$arr_http_result) {     // 获取配置文件失败
			if (!$cache || !isset($cache['meta']['expire'])) {
				throw new Exception('init cache fail'); //不可能走到该逻辑
			}
			// 1. 如果过期超过了 1 天，则总是发送错误日志
			// 2. 如果过期在1天内， 则 随机发送错误日志，避免错误日志量太大
			if (time() - $cache['meta']['expire'] > 86400 ||  rand(0, 500) == 0) {  // 如果存在cache，则尽量使用cache
				Sso_Sdk_Tools_Log::instance()->error('configcache', implode("\t", array('expire_too_long', $cache['meta']['expire'], $localstorage->get_storage_uri())));
			}
			return $cache;
		} else {
			if (isset($arr_http_result['data']['meta']['expire'])) {
				if ($arr_http_result['data']['meta']['expire'] < 86400 * 360) {    // 认为返回的是相对时间,转换为绝对时间
					$arr_http_result['data']['meta']['expire'] = time() + $arr_http_result['data']['meta']['expire'];
				}
			}
		}

		if ($cache && isset($arr_http_result['data']['data']['code']) && $arr_http_result['data']['data']['code'] == 304) {
			if (isset($arr_http_result['data']['meta']['expire'])) {
				$cache['meta']['expire'] = $arr_http_result['data']['meta']['expire'];  // 延长过期时间
				$should_update_cache = true;
			}
		}
		if($this->_is_valid_conf($arr_http_result['data'])) {
			$cache = $arr_http_result['data'];
			$should_update_cache = true;
		}

		if ($should_update_cache && !$localstorage->set($cache)) {
			Sso_Sdk_Tools_Log::instance()->error('configcache', 'update localstorage fail');
		}
		return $cache;
	}

	/**
	 * 检查是否为有效的配置
	 * @param array $arr
	 * @return bool
	 */
	private function _is_valid_conf(array $arr) {
		if (!isset($arr['data']) || !isset($arr['meta']) || !isset($arr['data']['main'])) return false;
		return true;
	}

	/**
	 * 远程获取配置信息
	 * @param null $etag
	 * @return mixed
	 * @throws Exception
	 */
	private function _get_conf_by_http($etag = null) {
		Sso_Sdk_Tools_Log::instance()->heartbeatlog_on_update_config();
		if (($timeout = self::get_user_config('timeout.http.timeout')) === null) {
			$timeout = (int)$this->_get_conf_default("data.timeout.http.timeout"); //这里似乎只能走默认值
		}
		$host = parse_url(self::$_config_url, PHP_URL_HOST);
		$proxy = Sso_Sdk_Config::get_user_config("proxy.".str_replace('.', '_', $host));

		$arr_query = array(
			'entry' => self::$_arr_user_config['entry'],
			'idc'   => self::$_arr_user_config['idc'],
			'domain'    => self::$_arr_user_config['domain'],
			'ip'    => Sso_Sdk_Tools_Util::get_client_ip(),
			'm'     => md5(self::$_arr_user_config['idc']. self::$_arr_user_config['domain']. self::$_arr_user_config['pin']),
			'ua'    => Sso_Sdk_Client::get_ua(),
			'uri'       => Sso_Sdk_Tools_Util::get_request_url(),    // 方便知道至少这次请求可以走到改SDK
			'sdk'       => __FILE__,
			'caller'    => Sso_Sdk_Tools_Util::get_sdk_caller(), //方便知道SDK位置
		);

		$http = new Sso_Sdk_Tools_Http(self::$_config_url);
		$http->set_method('post');
		$http->set_timeout($timeout);
		if ($proxy) {
			Sso_Sdk_Tools_Debugger::info($proxy, 'config http proxy');
			$http->set_proxy_server($proxy);
		}

		foreach ($arr_query as $k=>$v) {
			$http->add_post_field($k, $v);
		}
		$etag && $http->add_post_field('etag',$etag);

		$http->send();
		$content = $http->get_response_content();
		// 校验内容的合法性和完整性: 不检查
		$arr = @json_decode($content, true);
		if (!is_array($arr) || $arr['retcode'] !== 20000000) {
			$msg = "download config error: $content";
			Sso_Sdk_Tools_Log::instance()->error('configcache', $msg);
			throw new Exception($msg);
		}
		return $arr;
	}

	/**
	 * 获取默认的配置信息
	 * @param $key
	 * @return array|null
	 */
	private function _get_conf_default($key) {
		return Sso_Sdk_Tools_Util::get_key_in_array($key, $this->_arr_conf_default);
	}

	/**
	 * 检查配置是否已经初始化
	 * @return bool
	 */
	public static function is_init_already() {
		if (self::get_user_config('pin') == '') return false;
		return true;
	}
	/**
	 * 初始化部分已知的配置
	 */
	public static function init_user_config() {
		// 自动初始化idc信息
		$arr_config = array('idc' => isset($_SERVER['SINASRV_ZONE_IDC'])?strtolower(str_replace(range(0,9), '', $_SERVER['SINASRV_ZONE_IDC'])):null);
		// 自动初始化域名信息
		$host = Sso_Sdk_Tools_Request::server("HTTP_HOST"); // 对于设置HOST_MAP的场景，该逻辑可能不会凑效
		$arr = array("sina.com.cn", "weibo.com", "sina.cn", "weibo.cn");
		$domain = null;
		foreach($arr as $d) {
			if (Sso_Sdk_Tools_Util::is_sub_domain($host, $d)) {
				$domain = ".$d"; break;
			}
		}
		if ($domain) $arr_config['domain'] = $domain;
		Sso_Sdk_Config::set_user_config($arr_config);
	}

}


