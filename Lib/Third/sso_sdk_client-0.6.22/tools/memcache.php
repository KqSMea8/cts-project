<?php
/**
 * Memcache 的php实现
 * 开发说明：
 * 1. 关于crc32 + DISTRIBUTION_MODULA 的hash的实现  （可有可无）
 * 2. 关于连接的（错误）关闭、重用的测试
 * 3. 关于压缩、序列化的测试
 * 4. 连接失败的测试  ok       man connect  ECONNREFUSED 常用错误号
 * 5. 连接超时的测试  ok
 * 6. 读数据超时的测试
 * 7. 网络异常错误计数发生在该文件中，不要在上层实现
 */

class Sso_Sdk_Tools_Memcache {

	const E_CONFIG_NO_SERVERS       = 10500;
	const E_CONFIG_SERVER_ON_BAD    = 10501;

	const E_NETWORK_CONNECT_FAIL    = 10502;
	const E_NETWORK_CONNECT_TIMEOUT = 10503;
	const E_NETWORK_READ_TIMEOUT    = 10504;
	const E_NETWORK_DNS_LOOKUP_TIMEOUT    = 10505;
	const E_NETWORK_DNS_LOOKUP_FAIL = 10506;
	const E_HOST_PORT_FAIL               = 10507;

	const E_RES_SYSTEM_ERROR        = 10515;
	const E_RES_CLIENT_ERROR        = 10515;
	const E_RES_SERVER_ERROR        = 10516;
	const E_RES_WRITE_FAILURE       = 10517;
	const E_RES_READ_FAILURE        = 10518;
	const E_RES_NOTFOUND            = 10519;
	const E_RES_CONNECTION_SOCKET_CREATE_FAILURE    = 10520;
	const E_RES_PAYLOAD_FAILURE     = 10521;

	const E_RES_KEY_IS_EMPTY        = 10522;

	const OPT_CONNECT_TIMEOUT       = 1;
	const OPT_SEND_TIMEOUT          = 2;
	const OPT_RECV_TIMEOUT          = 3;
	const OPT_COMPRESSTHRESHOLD     = 4;
	const OPT_CONNECT_RETRY_ON_TIMEOUT = 5; //连接超时时重试选项

	const MMC_FLAG_COMPRESSED       = 16;    //存储数据是被压缩的

	private $_arr_hosts     = array();
	private $_arr_connect   = array();
	private $_arr_options   = array(
		self::OPT_CONNECT_TIMEOUT       => 1000,    //连接超时，单位： ms
		self::OPT_SEND_TIMEOUT          => 1000,    //发送超时，单位： ms
		self::OPT_RECV_TIMEOUT          => 1000,    //接收超时，单位： ms
		self::OPT_COMPRESSTHRESHOLD     => 100,     //超过多少Byte开启压缩功能， 单位： Byte

		self::OPT_CONNECT_RETRY_ON_TIMEOUT => 0,    //连接超时时重试次数，默认不重试
	);

	public function __construct() {}

	public function addServer($host, $port, $attr = array()) {
		$this->_arr_hosts[] = array(
			'host'  => $host,
			'port'  => $port,
			'attr'  => $attr,
		);
	}

	public function addServers($arr) {
		foreach($arr as $item) {
			$this->addServer($item['host'], $item['port'], $item['attr']);
		}
	}

	public function setOption($option, $value) {
		$this->_arr_options[$option] = $value;
	}
	public function set($key, $val, $expiration = 0) {
		return $this->setByKey($key, $key, $val, $expiration);
	}
	public function setByKey($hash_key, $key, $val, $expiration = 0) {
		$key = $this->_prepare_key($key);
		$fp = $this->_get_connection_by_hash($hash_key);
		$flag = 0;
		if (strlen($val) > $this->_arr_options[self::OPT_COMPRESSTHRESHOLD]) {
			$flag |= self::MMC_FLAG_COMPRESSED;
			$val = gzcompress($val);
		}
		$len = strlen($val);
		$cmd = "set $key $flag $expiration $len\r\n$val\r\n";
		$_arr = $this->_query($fp, $cmd);
		if ($_arr[0] !== 'STORED') {
			return false;
		}
		return true;
	}
	public function get($key) {
		return $this->getByKey($key, $key);
	}

	public function getByKey($hash_key, $key) {
		$key = $this->_prepare_key($key);
		$fp = $this->_get_connection_by_hash($hash_key);
		$slow_time = Sso_Sdk_Config::instance()->get('data.log.slowlog.mc.timeout');
		if (!isset($slow_time)) $slow_time = 500;
		$timer = Sso_Sdk_Tools_Timer::start('mc get');
		$cmd = "get $key\r\n";
		$_arr = $this->_query($fp, $cmd);
		if ($_arr[0] == 'END') {
			$time_use = Sso_Sdk_Tools_Timer::stop($timer, $slow_time);
			Sso_Sdk_Tools_Debugger::info("time use $time_use ms", 'mc get');
			throw new Exception('No Found', self::E_RES_NOTFOUND);
		}
		if ($_arr[0] !== 'VALUE' || $_arr[1] != $key) {
			throw new Exception('response error', self::E_RES_SYSTEM_ERROR);
		}
		$flag = $_arr[2];
		$value_len = $_arr[3];
		$value = '';
		if ($value_len > 0) $value = @fread($fp, $value_len); // value
		if ($value === false) {
			$arr_fp_meta = @stream_get_meta_data($fp);
			$remote = @stream_socket_get_name($fp, true);
			if (isset($arr_fp_meta['timed_out'])) {
				$this->_close($fp);
				$errno = self::E_NETWORK_READ_TIMEOUT;
				$errmsg = 'recv data timeout';
            } else {
				$errno = 0;
				$errmsg = 'unkown';
			}
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_MEMCACHE_ERROR, $remote);
			Sso_Sdk_Tools_Log::instance()->error("memcache", array($errno, $errmsg, $remote));
			throw new Exception($errmsg, $errno);
		} else {
			fread($fp, 2);  // \r\n
			fgets($fp);     // END\r\n

			if ($value_len > 0 && ($flag & self::MMC_FLAG_COMPRESSED)) {
				$value = gzuncompress($value);
			}
		}
		$time_use = Sso_Sdk_Tools_Timer::stop($timer, $slow_time);
		Sso_Sdk_Tools_Debugger::info("time use $time_use ms", 'mc get');
		return $value;
	}

	public function delete($key) {
		return $this->deleteByKey($key, $key);
	}

	public function deleteByKey($hash_key, $key) {
		$key = $this->_prepare_key($key);
		$fp = $this->_get_connection_by_hash($hash_key);
		$cmd = "delete $key\r\n";
		$_arr = $this->_query($fp, $cmd);
		if ($_arr[0] == 'NOT_FOUND') {
			throw new Exception('NOT_FOUND', self::E_RES_NOTFOUND);
		}
		if ($_arr[0] == 'DELETED') {
			return true;
		}
		throw new Exception('system error:'.$_arr[0], self::E_RES_SYSTEM_ERROR);
	}

	public function increment($key, $offset = 1, $initial = 0, $expiry = 0) {
		return $this->incrementByKey($key, $key, $offset, $initial, $expiry);
	}
	public function incrementByKey($hash_key, $key, $offset = 1, $initial = 0, $expiry = 0) {
		$key = $this->_prepare_key($key);
		$fp = $this->_get_connection_by_hash($hash_key);
		$cmd = "incr $key $offset\r\n";
		$_arr = $this->_query($fp, $cmd);
		if ($_arr[0] == 'NOT_FOUND') {
			$val = $initial + $offset;
			if (!$this->setByKey($hash_key, $key, $val, $expiry)) {
				return false;
			}
			return $val;
		}
		return $_arr[0];
	}

	public function close() {
		foreach($this->_arr_connect as $k=>$fp) {
			@fclose($fp);
			unset($this->_arr_connect[$k]);
		}
	}

	public function __destruct() {
		$this->close();
	}
	private function _prepare_key($key) {
		$key = str_replace(' ', '_', trim($key)); //key中不允许含有空格
		if (strlen($key) == 0) {
			throw new Exception('key is empty', self::E_RES_KEY_IS_EMPTY);
		}
		return $key;
	}
	private function _query($fp, $cmd) {
		$remote = @stream_socket_get_name($fp, true);   // catch 块儿中的$fp 可能已经被关闭了，所以该语句不能优化到catch块儿中
		try{
			$this->_send($fp, $cmd);
			return $this->_read_response_first_line($fp);
		} catch (Exception $e){
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_MEMCACHE_ERROR, $remote);
			Sso_Sdk_Tools_Log::instance()->error("memcache", array($e->getCode(), $e->getMessage(), $remote));
			throw new Exception($remote . ':'. $e->getMessage(), $e->getCode());
		}
	}
	private function _send($fp, $cmd) {
		if (false === fwrite($fp, $cmd, strlen($cmd))) {
			$this->_close($fp);
			throw new Exception('write data fail', self::E_RES_WRITE_FAILURE);
		}
	}
	private function _read_response_first_line($fp) {
		$head = fgets($fp);
		if ($head === false) {
			$arr_fp_meta = stream_get_meta_data($fp);
			if (isset($arr_fp_meta['timed_out'])) {
				$this->_close($fp);
				throw new Exception('recv data timeout', self::E_NETWORK_READ_TIMEOUT);
			}
			throw new Exception('read data fail', self::E_RES_READ_FAILURE);
		}
		$head = substr($head, 0, strlen($head) - 2);    // 去掉\r\n
		$_arr = explode(' ', $head);
		switch($_arr[0]) {
			case 'ERROR':   // nonexistent command name
				throw new Exception('system error', self::E_RES_SYSTEM_ERROR);
			case 'CLIENT_ERROR':
				throw new Exception($_arr[1], self::E_RES_CLIENT_ERROR);
			case 'SERVER_ERROR':
				throw new Exception($_arr[1], self::E_RES_SERVER_ERROR);
		}
		return $_arr;
	}
	private function _get_connection_by_hash($hash_key) {
		if (count($this->_arr_hosts) == 0) {
			throw new Exception('server no config', self::E_CONFIG_NO_SERVERS);
		}
		$mod = 0;
		if (count($this->_arr_hosts) > 1){
			$hash = (crc32($hash_key) >> 16) & 0x7fff; // 和libmemcached中的crc32相同
			$mod = $hash % count($this->_arr_hosts);
		}
		$arr_server = $this->_arr_hosts[$mod];
		if ($arr_server['attr']['status'] != 'ok') {	// 此种情况不进行错误计数，否则就没法做服务的缓慢恢复了
			throw new Exception('server is not ready', self::E_CONFIG_SERVER_ON_BAD);
		}
		if (!isset($this->_arr_connect[$mod])) {
			try{
				$fp = $this->_connect($arr_server['host'], $arr_server['port']);
			} catch (Exception $e){
				throw $e;
			}
			// 选项设置
			@stream_set_timeout($fp, 0, $this->_arr_options[self::OPT_RECV_TIMEOUT] * 1000);
			$this->_arr_connect[$mod] = $fp;
		}
		return $this->_arr_connect[$mod];
	}

	private function _dns_lookup($domain) {
		if (preg_match('#[0-9]$#', $domain)) return $domain;    //数字结尾的视为IP
		$dns_lookup_slowlog_timeout = Sso_Sdk_Config::instance()->get('data.log.slowlog.dns.timeout');
		$dns_lookup_timeout = Sso_Sdk_Config::instance()->get('data.timeout.dns.timeout');
		if (!$dns_lookup_slowlog_timeout) $dns_lookup_slowlog_timeout = 1000;

		if (!Sso_Sdk_Tools_Counter::is_ok(Sso_Sdk_Tools_Counter::TYPE_DNS_LOOKUP_ERROR, $domain)) {
			throw new Exception("dns lookup fail: $domain", self::E_NETWORK_DNS_LOOKUP_FAIL);
		}
		//因为dns查询是以秒为单位的，不太可控，所以，这里做一个超时计数，如果总是查询很慢则会自动降级
		if (!Sso_Sdk_Tools_Counter::is_ok(Sso_Sdk_Tools_Counter::TYPE_DNS_LOOKUP_TIMEOUT, $domain)) {
			throw new Exception("dns lookup timeout: $domain", self::E_NETWORK_DNS_LOOKUP_FAIL);
		}
		//如果$host是域名，则先解析域名再连接，使用gethostbyname解析域名的效率会高一些，这里只解析IPv4地址，如果解析失败，则原串儿返回,而且，/etc/hosts中的设置是可以生效的
		//这样的话，重新连接时也不需要在做域名解析了
		$timer = Sso_Sdk_Tools_Timer::start('mc_dns_lookup.'.$domain);
		$host = gethostbyname($domain); // 该函数的超时设置值取决于 /etc/resolve.conf
		$dns_time_use = Sso_Sdk_Tools_Timer::stop($timer, $dns_lookup_slowlog_timeout);
		Sso_Sdk_Tools_Debugger::info("$domain=>$host use time $dns_time_use ms", 'mc dns lookup');
		if ($dns_time_use > $dns_lookup_timeout) { // 这里只做计数，不抛异常，能解析出来就使用
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_DNS_LOOKUP_TIMEOUT, $domain);
		}
		if ($host == $domain) {
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_DNS_LOOKUP_ERROR, $domain);
			Sso_Sdk_Tools_Log::instance()->error("dns_lookup", array($domain, $dns_time_use));
			throw new Exception("dns lookup fail [$domain]", self::E_NETWORK_DNS_LOOKUP_FAIL);
		}
		return $host;
	}
	private function _connect($host, $port) {
		$options = $this->_arr_options;
		$connect_timeout = $options[self::OPT_CONNECT_TIMEOUT]/1000;
		$retry = $options[self::OPT_CONNECT_RETRY_ON_TIMEOUT];

		$host = $this->_dns_lookup($host);  // 如果出错，这里会抛出一个合适的异常

		$remote = "$host:$port";
		if (!Sso_Sdk_Tools_Counter::is_ok(Sso_Sdk_Tools_Counter::TYPE_MEMCACHE_ERROR, $remote)) {
			throw new Exception("memcache fail: $remote", self::E_HOST_PORT_FAIL);
		}

		do{
			$timer = Sso_Sdk_Tools_Timer::start('mc_connect');
			// 说明： stream_socket_client(...) 中如果需要解析域名的话，连接超时设置并没有包括（也不作用于）域名解析时间
			$fp = @stream_socket_client("tcp://$host:$port", $errno, $error, $connect_timeout, STREAM_CLIENT_CONNECT);
			$mc_slow_timeout = Sso_Sdk_Config::instance()->get('data.log.slowlog.mc.timeout');
			if (!$mc_slow_timeout) $mc_slow_timeout = 0;
			$connect_time_use = Sso_Sdk_Tools_Timer::stop($timer, $mc_slow_timeout);

			Sso_Sdk_Tools_Debugger::info("$remote use time $connect_time_use ms", 'mc connect');
			if ($errno != 0){
				Sso_Sdk_Tools_Debugger::warn("$remote $errno $error", 'mc connect fail');
				Sso_Sdk_Tools_Log::instance()->warn('memcache', array('connect', $remote, $retry, $options[self::OPT_CONNECT_RETRY_ON_TIMEOUT], $connect_timeout));
			}
		} while ($errno == 110 && $retry-- > 0);
		if (!$fp) { // $errno 常见错误号参看： /usr/include/asm-generic/errno.h 如： #define ETIMEDOUT       110
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_MEMCACHE_ERROR, $remote);
			Sso_Sdk_Tools_Log::instance()->error('memcache', array('reconnect', $remote, $retry, $options[self::OPT_CONNECT_RETRY_ON_TIMEOUT], $connect_timeout));
			throw new Exception($errno.':'.$error." [$remote]", $errno == 110?self::E_NETWORK_CONNECT_TIMEOUT:self::E_NETWORK_CONNECT_FAIL);
		}
		return $fp;
	}

	private function _close($fp) {
		@fclose($fp);
		foreach($this->_arr_connect as $k=>$v) {
			if ($v == $fp) unset($this->_arr_connect[$k]);
		}
	}
}