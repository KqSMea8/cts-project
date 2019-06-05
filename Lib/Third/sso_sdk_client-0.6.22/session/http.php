<?php
class Sso_Sdk_Session_Http {

	const HTTP_SUCC = 20000000;
	const E_VALIDATE_INVALID = 50111309;
	const E_VALIDATE_SCF_INVALID = 50111310;
	const E_QUERY_LOCK_STATUS = 50111311;
	const E_NETWORK = 10100;
	const E_RESPONSE_DATA = 10101;
	const E_UNKNOWN = 10102;	// 服务器返回的未知错误

	private static $_instance;

	private $_entry;
	private $_pin;
	private $_domain;
	private $_idc;
	private $_from;
	private $_aid;

	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		$this->_entry = Sso_Sdk_Config::instance()->get_user_config('entry');
		$this->_pin = Sso_Sdk_Config::instance()->get_user_config('pin');
		$this->_domain = Sso_Sdk_Config::instance()->get_user_config('domain');
		$this->_idc = Sso_Sdk_Config::instance()->get_user_config('idc');
		$this->_from = Sso_Sdk_Tools_Request::server('HTTP_HOST').Sso_Sdk_Tools_Request::server('PHP_SELF');
		$this->_aid = Sso_Sdk_Tools_Request::request('aid');	// 不需要过滤,服务端会校验
	}

	/**
	 * 
	 * @param  $scf (aid)
	 * @param  $sub
	 * @param  $domain
	 */
	public function is_credible($scf, $sub) {

		if (!Sso_Sdk_Tools_Counter::is_ok(Sso_Sdk_Tools_Counter::TYPE_HTTP_VALIDATE_ERROR)) {
			throw new Exception(self::E_NETWORK);
		}

		$arr_query = array(
			'scf'  => $scf,
			'entry' => $this->_entry,
			'sub'   => $sub,
			'sub_domain'    => $this->_domain,
			'idc'    => $this->_idc,
			'ip'    => Sso_Sdk_Tools_Util::get_client_ip(),
			'sign' => md5($sub. $this->_domain. $scf . $this->_pin),
		);

		try {
			$url = Sso_Sdk_Config::instance()->get("data.res.http.scf_validate");
			$result = $this->_request($url, 'POST', $arr_query);
		} catch (Exception $e) {
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_HTTP_VALIDATE_ERROR);
			throw $e;
		}
		if ($result['retcode'] != self::HTTP_SUCC) {
			throw new Exception('scf invalid', self::E_VALIDATE_INVALID);
		}
		
		return $result['data']['credible'];
	}

	/**
	 * @param  $sub
	 */
	public function get_lock_status($sub){

		if (!Sso_Sdk_Tools_Counter::is_ok(Sso_Sdk_Tools_Counter::TYPE_HTTP_LOCK_QUERY_ERROR)) {
			throw new Exception(self::E_NETWORK);
		}

		$arr_query = array(
			'entry' 		=> $this->_entry,
			'sub' 			=> $sub,
			'sub_domain'	=> $this->_domain,
			'idc'			=> $this->_idc,
			'ip'			=> Sso_Sdk_Tools_Util::get_client_ip(),
			'sign'			=> md5($sub. $this->_domain. $this->_pin),
		);

		try {
			$url = Sso_Sdk_Config::instance()->get("data.res.http.lock_status");
			$result = $this->_request($url, 'POST', $arr_query);
		} catch (Exception $e) {
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_HTTP_LOCK_QUERY_ERROR);
			throw $e;
		}
		if ($result['retcode'] != self::HTTP_SUCC) {
			throw new Exception('query lock status error', self::E_QUERY_LOCK_STATUS);
		}

		return $result['data'];

	}
	
	/**
	 * 更新sid
	 * @param $sid string
	 * @return array
	 */
	public function renew($sid) {
		return $this->_validate($sid, true);
	}
	/**
	 * @param $sid string
	 * @return array
	 * @throws Exception
	 */
	public function validate($sid) {
		return $this->_validate($sid, false);
	}

	/**
	 * @param $sid string
	 * @return array|bool
	 * @throws Exception
	 */
	public function destroy_by_sid($sid) {

		if (!Sso_Sdk_Tools_Counter::is_ok(Sso_Sdk_Tools_Counter::TYPE_HTTP_DESTROY_ERROR)) {
			return false;
		}

		$arr_query = array(
			'entry' => $this->_entry,
			'sid'   => $sid,
			'domain'    => $this->_domain,
			'ip'    => Sso_Sdk_Tools_Util::get_client_ip(),
			'm' => md5($sid. $this->_domain. $this->_pin),
			'from' => $this->_from,
			'aid' => $this->_aid,
		);

		try {
			$url = Sso_Sdk_Config::instance()->get("data.res.http.destroybysid");
			$result = $this->_request($url, 'POST', $arr_query);
		} catch (Exception $e) {
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_HTTP_DESTROY_ERROR);
			throw $e;
		}
		if ($result['retcode'] != self::HTTP_SUCC) {
			Sso_Sdk_Tools_Log::instance()->error('destroy', 'destroy fail');
			return false;
		}
		return true;
	}

	/**
	 * 校验sid的有效性
	 * @param $sid string
	 * @param bool $renew 是否强制更新
	 * @throws Exception
	 * @return array
	 */
	private function _validate($sid, $renew = false) {

		if (!Sso_Sdk_Tools_Counter::is_ok(Sso_Sdk_Tools_Counter::TYPE_HTTP_VALIDATE_ERROR)) {
			if (mt_rand(0, 999) == 0) {
				Sso_Sdk_Tools_Log::instance()->warn("validate_network_warn", $sid);
			}
			throw new Exception(self::E_NETWORK);
		}

		$arr_query = array(
			'entry' => $this->_entry,
			'sub'   => $sid,
			'domain'    => $this->_domain,
			'idc'    => $this->_idc,
			'ip'    => Sso_Sdk_Tools_Util::get_client_ip(),
			'm' => md5($sid. $this->_domain. $this->_pin),
			'from' => $this->_from,
			'aid' => $this->_aid,
		);

		if ($renew) $arr_query['renew'] = 1;
		$allow_renew = Sso_Sdk_Config::get_user_config('allow_renew');
		if (!$allow_renew) $arr_query['renew'] = -1;    //强制不允许renew

		try {
			$url = Sso_Sdk_Config::instance()->get("data.res.http.validate");
			$result = $this->_request ($url, 'POST', $arr_query);
		} catch (Exception $e) {
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_HTTP_VALIDATE_ERROR);
			throw $e;
		}

		if ($result['retcode'] == self::E_VALIDATE_INVALID) {  // session 未找到、或不匹配、或无效，就是验证失败
			//这里不需要日志，因为server端知道
			throw new Exception("invalid", self::E_VALIDATE_INVALID);
		}

		if ($result['retcode'] != self::HTTP_SUCC) {
			throw new Exception('invalid', self::E_VALIDATE_INVALID); //这里原本是想放过的，似乎不能放过，所以多写了几行
		}

		if (isset($result['data']['meta']) && isset($result['data']['meta']['refresh_config_cache'])) {
			Sso_Sdk_Config::get_localstorage()->delete();
		}
		return $result;
	}

	/**
	 * @param $sid
	 * @param bool $renew
	 * @return array
	 * @throws Exception
	 */
	public function v3_validate($sid, $renew = false) {

		if (!Sso_Sdk_Tools_Counter::is_ok(Sso_Sdk_Tools_Counter::TYPE_HTTP_V3_VALIDATE_ERROR)) {
			// 首先从降级配置上看,访问不频繁的机器是不易触发降级机制的.而对于访问频繁的机器,
			// 按照 PHP QPS 单机通常过千即上限的估算, 0.1% 回传概率应该比较合适.即单机每秒回传一条降级预警日志.
			// 即便触发回传概率低于预期,整个降级周期只要能触发至少一次即可接受.所以回传概率过高无益.
			if (mt_rand(0, 999) == 0) {
				Sso_Sdk_Tools_Log::instance()->warn("v3_validate_network_warn", $sid);
			}
			throw new Exception("network error(v3)", self::E_NETWORK);
		}

		$arr_query = array(
			'entry' => $this->_entry,
			'sub' => $sid,
			'domain' => $this->_domain,
			'idc' => $this->_idc,
			'renew' => ($renew ? 1 : 0),
			'ip' => Sso_Sdk_Tools_Util::get_client_ip(),
			'm' => md5($sid . $this->_domain . $this->_pin),
			'from' => $this->_from,
			'aid' => $this->_aid,
		);

		try {
			$config = Sso_Sdk_Config::instance();
			$url = $config->get("data.res.http.v3_validate");
			$options['conn_timeout'] = $config->get("data.main.session.use_v3_api_conn_timeout");
			$options['timeout'] = $config->get("data.main.session.use_v3_api_timeout");
			$result = $this->_request($url, 'POST', $arr_query, $options);
		} catch (Exception $e) {
			// 两种情况: 1.网络异常; 2.响应数据错误; 考虑降级一律识别为网络异常.
			Sso_Sdk_Tools_Counter::incr(Sso_Sdk_Tools_Counter::TYPE_HTTP_V3_VALIDATE_ERROR);
			Sso_Sdk_Tools_Log::instance()->error("v3_validate_http_error", $e->getMessage());
			throw $e;
		}

		// 上面必然会返回 retcode 字段
		switch ($result['retcode']) {

			case self::HTTP_SUCC:
				return $result;

			case self::E_VALIDATE_INVALID:
				throw new Exception("invalid", self::E_VALIDATE_INVALID);

			default:	// 其他错误一律识别为未知错误,上层会选择性放过.
				throw new Exception('unknown: '.$result['retcode'], self::E_UNKNOWN);
		}

	}

	/**
	 * @param $sid
	 * @return bool
	 * @throws Exception
	 */
	public function v3_destroy($sid) {

		$arr_query = array(
			'entry' => $this->_entry,
			'sid' => $sid,
			'domain' => $this->_domain,
			'ip' => Sso_Sdk_Tools_Util::get_client_ip(),
			'm' => md5($sid . $this->_domain . $this->_pin),
			'from' => $this->_from,
			'aid' => $this->_aid,
		);

		try {
			$config = Sso_Sdk_Config::instance();
			$url = $config->get("data.res.http.v3_destroy");
			$options['conn_timeout'] = $config->get("data.main.session.use_v3_api_conn_timeout");
			$options['timeout'] = $config->get("data.main.session.use_v3_api_timeout");
			$result = $this->_request($url, 'POST', $arr_query, $options);
			if ($result['retcode'] == self::HTTP_SUCC) {
				return true;
			}
		} catch (Exception $e) {
			// 失败可接受,不抛异常
			$message = $e->getCode().",".$e->getMessage();
			Sso_Sdk_Tools_Log::instance()->error('v3_destroy', $message);
		}
		return false;
	}

	/**
	 * @param $url
	 * @param string $http_method
	 * @param array $data
	 * @param array $options
	 * @return array
	 * @throws Exception
	 */
	private function _request($url, $http_method='GET', array $data=array(), $options = array()) {
	    
	    $config = Sso_Sdk_Config::instance();

		// 定义为环境信息更合适,目前仅包含设备信息,默认补齐全部回传服务端
		//$arr_user_validate_query = $config::get_user_config('validatesid_query');
		if (is_array($arr_user_validate_query)) {
			foreach ($arr_user_validate_query as $key => $val) {
				if ($val !== null) {
					$data[$key] = $val;
				}
			}
		}

		// 失败则重试
		$content = "";
		$retry_num = intval($config->get("data.main.session.use_v3_api_retry_num"));
		$exec_num = ($retry_num > 0 && $retry_num < 3) ? ($retry_num + 1) : 1;         // 请求执行次数，取值 1 ~ 3 次
		$micro_sec = intval($config->get("data.main.session.use_v3_api_retry_interval"));
		$micro_sec = ($micro_sec > 0 && $micro_sec <= 50000) ? $micro_sec : 0;         // 请求执行间隔，取值 0 ~ 50 ms
		for ($i = 0; $i < $exec_num; $i++) {
		    $content = Sso_Sdk_Tools_Http::request($url, $http_method, $data, $options);
		    if (Sso_Sdk_Tools_Http::$last_error_no == CURLE_OK) {
		        break;
		    }
		    if ($micro_sec > 0) {
		        usleep($micro_sec);
		    }
		}
		if (Sso_Sdk_Tools_Http::$last_error_no !== CURLE_OK) {
			$message = Sso_Sdk_Tools_Http::$last_error_no.",".Sso_Sdk_Tools_Http::$last_error_msg;
			$message .= "|".json_encode(Sso_Sdk_Tools_Http::$last_request_log);
			// 这里不发日志了,如果上层应用关心就自己发.要考虑网络异常对丢包的影响.
			throw new Exception("network error: ".$message, self::E_NETWORK);
		}

		/** @var $result array */
		$result = @json_decode($content, true);   // 解析json
		if (!$result || !is_array($result) || !isset($result['retcode'])) {
			$content_value = var_export($content, true);
			Sso_Sdk_Tools_Log::instance()->error("httpresponse", "content: $content_value");
			throw new Exception("response data invalid, content: $content_value", self::E_RESPONSE_DATA);
		}
		return $result;
	}

}
