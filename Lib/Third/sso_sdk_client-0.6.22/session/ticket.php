<?php

/**
 * Class Sso_Sdk_Session_Ticket
 */
class Sso_Sdk_Session_Ticket {

	/**
	 * 校验ticket
	 * @param $ticket
	 * @param array $arr_extra
	 * @throws Exception
	 * @return mixed
	 */
	public static function validate($ticket, array $arr_extra = array()) {
		$arr_query = array(
			'service'	=> Sso_Sdk_Config::get_user_config('service'),
			'ticket'	=> $ticket,
			'domain'	=> Sso_Sdk_Config::get_user_config('domain'),
			'ip'		=> Sso_Sdk_Tools_Util::get_client_ip(),
			'agent'		=> Sso_Sdk_Tools_Request::server('HTTP_USER_AGENT')
		);
		$dinfo = Sso_Sdk_Config::get_user_config('dinfo');
		if ($dinfo) $arr_query['dinfo'] = $dinfo;
		if ($arr_extra) {
			$arr_query = array_merge($arr_query, $arr_extra);
		}
		$url = Sso_Sdk_Config::instance()->get('data.res.http.validate_ticket');
		$content = Sso_Sdk_Tools_Http::request($url, "post", $arr_query);
		if ($content === false) {
			throw new Exception('network exception');
		}
		$arr = @json_decode($content, true);
		if (!is_array($arr) || !isset($arr['retcode'])) {
			throw new Exception('response error');
		}
		if ($arr['retcode'] != 0) {
			throw new Exception('invalid', $arr['retcode']);
		}
		return $arr;
	}
}