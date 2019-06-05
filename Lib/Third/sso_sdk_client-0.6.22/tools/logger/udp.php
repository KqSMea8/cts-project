<?php
/**
 * 记录日志的udp实现
 * 日志格式： (数字都是16进制表示)
 * 时间(Mar 28 14:58:44) ip(luna245) tag(ssoclient) version(1.0.0) msgid(r17) packagenum(1) sequence(0) msglen(3) signed(847c) type(timeout) content(xxx)
 * Mar 28 15:03:50 luna245 ssoclient 1.0.0 r17 1 0 3 847c timeout msg
 * syslog协议参考： http://en.wikipedia.org/wiki/Syslog
 */

class Sso_Sdk_Tools_Logger_UDP implements Sso_Sdk_Tools_Logger_ILogger {
	/**
	 * 日志级别定义
	 * @var array
	 */
	private $_arr_log_level = array(
		"emerg"     => 0,
		"alert"     => 1,
		"crit"      => 2,
		"error"     => 3,
		"warn"      => 4,
		"notice"    => 5,
		"info"      => 6,
		"debug"     => 7,
	);

	/**
	 * 日志族定义
	 * @var array
	 */
	private $_arr_log_facility = array(
		"kern"      => 0,
		//... 这里可以参考：http://en.wikipedia.org/wiki/Syslog 补全
		"local0"    => 16,
		"local1"    => 17,
		"local2"    => 18,
		"local3"    => 19,
		"local4"    => 20,
		"local5"    => 21,
		"local6"    => 22,
		"local7"    => 23,
	);

	/**
	 * 记录日志
	 * @param $level int
	 * @param $type string
	 * @param $msg string
	 * @throws Exception
	 * @return mixed
	 */
	public function log($level, $type, $msg) {
		if ($recvlevel = Sso_Sdk_Config::instance()->get("data.main.log.recvlevel")) {
			if (!in_array($level, explode(',', $recvlevel))) return false; //拒绝某些级别的日志
		}
		if (is_array($msg)) $msg = @json_encode($msg);

		$facility = Sso_Sdk_Config::instance()->get("data.main.log.facility");

		if (!isset($this->_arr_log_facility[$facility], $this->_arr_log_level[$level], $msg)) return false;
		$pri = ($this->_arr_log_facility[$facility] << 3) + $this->_arr_log_level[$level];
		$arr_header = array(
			'pri'   => "<$pri>",
			'data'  => date('M d H:i:s'),
			'ip'    => Sso_Sdk_Tools_Util::get_server_ip(),
			'tag'   => 'ssoclient',
			'ssoclient_version' => Sso_Sdk_Client::get_version(),
		);

		$msgid = $this->_make_msgid();
		$header = implode(" ", $arr_header);

		$msg = $this->_get_baseinfo(). "\t". $msg;
		$sequenceid = 0;
		$arrMsg = $this->_packet($msg);
		$package_num = count($arrMsg);

		$signedkey = Sso_Sdk_Config::instance()->get("data.main.log.signedkey");
		foreach($arrMsg as $line) {
			$signed = substr(md5($line. $signedkey), 0,2);

			$len = strlen($line);
			$_msg = implode(' ', array(
				$header, $msgid, dechex($package_num), dechex($sequenceid), dechex($len), $signed, $type, $line
			));
			$this->_send($_msg, $level);

			$sequenceid++;
		}
		return true;
	}

	private function _send($msg, $level) {
		if ($level == 'info'){
			$arr = Sso_Sdk_Config::instance()->get("data.res.udp.info");
		} else {
			$arr = Sso_Sdk_Config::instance()->get("data.res.udp.error");
		}
		if (!$arr || !is_array($arr) || count($arr) == 0) return false;
		$_arr = array();
		foreach($arr as $item) {
			if ($item['enable'] == true) {
				if (!$item['host'] || !$item['port']) continue;
				$_arr[] = array('host' => $item['host'], 'port'=> $item['port']);
			}
		}
		if (count($_arr) == 0) return false;
		shuffle($_arr);
		$_arr = array_shift($_arr);

		if (!isset($_arr['host']) || !isset($_arr['port'])) return false;
		$host = $_arr['host'];
		$port = $_arr['port'];
		if (!$host || !$port) return false;
		$fp = @stream_socket_client("udp://$host:$port");
		@fwrite($fp, $msg, strlen($msg));
		return true;
	}

	/**
	 * 每次日志都需要发送的基本信息
	 */
	private function _get_baseinfo() {
		$arr = array(
			'entry' => Sso_Sdk_Config::get_user_config('entry'),
			'client_ip' => Sso_Sdk_Tools_Util::get_client_ip(),
		);
		return implode("\t", $arr);
	}
	/**
	 * 对要传输日志做预处理
	 * 1.对数组做序列化
	 * 2.对超长的日志做分组处理
	 * @param $msg
	 * @return array
	 */
	private function _packet($msg) {

		$arr_result = array();
		foreach(explode("\n", $msg) as $line) {
			if (strlen($line) > 1024) { //方便网络传输，允许最大1k的数据包（不包括meta信息）
				foreach(str_split($line, 1024) as $_line) {
					$arr_result[] = $_line;
				}
			} else {
				$arr_result[] = $line;
			}
		}
		return $arr_result;
	}
	/**
	 * 当消息太长分包发送时，方便查找一个完整的消息，虽然并不会对包进行重组
	 * @return string
	 */
	private function _make_msgid() {
		return Sso_Sdk_Tools_String::rand(Sso_Sdk_Tools_String::LONUM, 3);
	}
}