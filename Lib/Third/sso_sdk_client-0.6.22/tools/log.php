<?php
/**
 * 负责记录日志
 */

/**
 * Class Sso_Sdk_Tools_Log
 */
class Sso_Sdk_Tools_Log {


	/** @var Sso_Sdk_Tools_Log */
	private static $_instance;
	/** @var \Sso_Sdk_Tools_Logger_UDP  */
	private $_udp;
	/**
	 * @return Sso_Sdk_Tools_Log
	 */
	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 私有构造函数，避免外部实例化
	 */
	private function __construct() {
		$this->_udp = new Sso_Sdk_Tools_Logger_UDP();
	}

	/**
	 * 写info级别的日志
	 * @param $type string
	 * @param $msg string|array
	 * @return bool
	 */
	public function info($type, $msg) {
		return $this->_send("info", $type, $msg);
	}

	/**
	 * 写notice级别的日志
	 * @param $type string
	 * @param $msg string|array
	 * @return bool
	 */
	public function notice($type, $msg) {
		return $this->_send("notice", $type, $msg);
	}

	/**
	 * 写warn级别的日志
	 * @param $type string
	 * @param $msg string|array
	 * @return bool
	 */
	public function warn($type, $msg) {
		return $this->_send("warn", $type, $msg);
	}

	/**
	 * 写debug级别的日志
	 * @param $type string
	 * @param $msg string|array
	 * @return bool
	 */
	public function debug($type, $msg) {
		return $this->_send("debug", $type, $msg);
	}

	/**
	 * 写error级别的日志
	 * @param $type string
	 * @param $msg string|array
	 * @return bool
	 */
	public function error($type, $msg) {
		return $this->_send("error", $type, $msg);
	}

	/**
	 * 写用户访问信息日志
	 * @param $uid int|string
	 * @return bool
	 */
	public function accesslog($uid) {
		if(!$uid) return false;
		if (Sso_Sdk_Config::instance()->get('data.main.log.accesslog') !== true) return false;
		if(!$this->_pass_by_accesslog_byuid($uid)
			&& true) {
			return false;
		}
		$tmpl = Sso_Sdk_Config::instance()->get('data.log.accesslog');
		if (!is_array($tmpl)) return false;
		$arr = array('uid'=>$uid);

		foreach($tmpl as $key=>$item) {
			$_arr = eval('return $'.$key.';');

			if (!is_array($_arr) || !is_array($item) || empty($_arr) || empty($item)) continue;
			foreach($item as $_k) {
				if(isset($_arr[$_k])) $arr[$key][$_k] = $_arr[$_k];
			}
		}
		if (count($arr) == 0) return false;
		$this->info('accesslog', $arr);
		return true;
	}

	/**
	 * 根据uid配置要收集那些日志
	 * @param $uid int|string
	 * @return bool
	 */
	private function _pass_by_accesslog_byuid($uid) {
		$prefix = 'data.log.rule.accesslog.byuid';
		if (Sso_Sdk_Config::instance()->get("$prefix.model.ereg.enable") === true) {
			$arr = Sso_Sdk_Config::instance()->get("$prefix.model.ereg.option");
			foreach($arr as $ereg) {
				if (preg_match($ereg, $uid)) {
					return true;
				}
			}
		}
		if (Sso_Sdk_Config::instance()->get("$prefix.model.mod.enable") === true) {
			$arr = Sso_Sdk_Config::instance()->get("$prefix.model.mod.option");
			foreach($arr as $item) {
				if (!is_array($item) || !isset($item['max']) || $item['max'] <= 0 || !is_array($item['mod'])) continue;
				if (in_array($uid%$item['max'], $item['mod'])) {
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * 写错误信息日志，附带一些其他可能有用的信息
	 * @return bool
	 */
	public function errorlog() {
		if (Sso_Sdk_Config::instance()->get('data.main.log.errorlog') !== true) return false;
		return true;
	}

	/**
	 * 写访问超时类日志
	 * @param $type string
	 * @param $time int ms
	 * @param $threshold int ms
	 * @return bool
	 */
	public function timelog($type, $time, $threshold) {
		if (Sso_Sdk_Config::instance()->get('data.main.log.timelog') !== true) return false;
		$this->notice('timelog', implode(' ', array($type, (int)$time, $threshold)));
		return true;
	}

	/**
	 * 根据版本号的配置发送心跳日志
	 * @return bool
	 */
	public function heartbeatlog_by_version_conf() {
		if (Sso_Sdk_Config::instance()->get('data.main.log.heartbeatlog') !== true) return false;
		$version = Sso_Sdk_Client::get_version();
		$arr = Sso_Sdk_Config::instance()->get('data.log.heartbeatlog');
		if (!is_array($arr)) return false;
		$allow = false;
		if (isset($arr['min']) && $version <= $arr['min']) {
			$allow = true;
		}
		if (isset($arr['max']) && $version >= $arr['max']) {
			$allow = true;
		}
		if (isset($arr['in']) && is_array($arr['in']) && in_array($version, $arr['in'])) {
			$allow = true;
		}
		if ($allow == false) return false;
		return $this->heartbeatlog();
	}

	/**
	 * 重新获取配置文件时发送心跳日志
	 * @return bool
	 */
	public function heartbeatlog_on_update_config() {
		Sso_Sdk_Tools_Debugger::info(Sso_Sdk_Config::instance()->get('data.main.log.heartbeatlog_on_update_config'), "heartbeatlog_on_update_config");
		if (Sso_Sdk_Config::instance()->get('data.main.log.heartbeatlog_on_update_config') !== true) return false;
		return $this->heartbeatlog();
	}
	/**
	 * 发送心跳日志
	 * @return bool
	 */
	public function heartbeatlog() {
		$version = Sso_Sdk_Client::get_version();
		$arr = array(
			'version'   => $version,
			'uri'       => Sso_Sdk_Tools_Util::get_request_url(),
			'sdk'       => __FILE__,
			'caller'    => Sso_Sdk_Tools_Util::get_sdk_caller(), //SDK 位置和调用者位置
		);
		$this->notice('heartbeatlog', $arr);
		return true;
	}
	/**
	 * 统一的发送日志的私有方法
	 * @param $level
	 * @param $type
	 * @param $msg
	 * @return bool
	 * @throws Exception
	 */
	private function _send($level, $type, $msg) {
		$type = str_replace(' ', '_', $type);   //将type可能存在的空格替换成下划线，方便日志分析
		if (Sso_Sdk_Config::get_logger() !== null && $level !== 'info') {
			Sso_Sdk_Config::get_logger()->log($level, $type, $msg);
		}
		return $this->_udp->log($level, $type, $msg);
	}

}