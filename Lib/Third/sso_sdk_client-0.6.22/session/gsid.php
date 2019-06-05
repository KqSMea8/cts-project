<?php
/**
 * gsid校验类
 * Class Sso_Sdk_Session_Gsid
 */
class Sso_Sdk_Session_Gsid {

	const COOKIE_NAME = 'gsid_CTandWM';

	public static function validate($gsid, $arr_extra = array()) {

		if (Sso_Sdk_Config::get_user_config('remote_validate') == false)  { //配置不开启远程校验，做本地校验
			return self::local_validate($gsid);
		}

		$url = Sso_Sdk_Config::instance()->get('data.res.http.checkgsid');
		$pin = Sso_Sdk_Config::instance()->get('data.key.checkgsid');
		$ip = Sso_Sdk_Tools_Util::get_client_ip();
		$t = md5($pin . $gsid . $ip);
		$arr_query = array(
			"gsid" => $gsid,
			"t" => $t,
			"cip" => $ip,
			"proj" => 1,
			"ivalue" => Sso_Sdk_Tools_Request::server('HTTP_USER_AGENT'),
		);
		isset($arr_extra['proj']) && $arr_query["proj"] = $arr_extra['proj'];
		isset($arr_extra['ivalue']) && $arr_query['ivalue'] = $arr_extra['ivalue'];
		$result = Sso_Sdk_Tools_Http::request($url, 'get', $arr_query);
		/*
		 * succ: {"result":{"uniqueid":"103630","name":"103630","displayname":"\u674e\u4fca\u6770"},"errno":0,"error":"ok"}
		 * fail: {"errno":1,"error":"params miss or auth fail!"}
		 */
		$arr = @json_decode($result, true);
		if (!$arr || !is_array($arr)) {
			$msg = 'http response fail :'. $result;
			Sso_Sdk_Tools_Log::instance()->warn('gsid_validate', implode(',', array('fail', $gsid, $result)));
			throw new Exception($msg);
		}
		if ($arr['errno'] != 0) {
			Sso_Sdk_Tools_Log::instance()->notice('gsid_validate', implode(',', array('fail', $gsid, $result)));
			throw new Exception($arr['error'], $arr['errno']);
		}
		return array('uid'=>$arr['result']['uniqueid']);
	}

	/**
	 * 销毁gsid
	 * @param $gsid string
	 * @return bool
	 * @throws Exception
	 */
	public static function destroy($gsid) {
		$url = Sso_Sdk_Config::instance()->get('data.res.http.destroygsid');
		$pin = Sso_Sdk_Config::instance()->get('data.key.checkgsid'); //pin和校验时的一样
		$t = md5($pin . $gsid);
		$arr_query = array(
			'gsid' => $gsid,
			't' => $t,
		);
		$result = Sso_Sdk_Tools_Http::request($url, 'get', $arr_query);
		/*
		 * succ: {"errno":0,"error":"ok"}
		 * fail: {"errno":1,"error":"params miss or auth fail!"}
		 */
		$arr = @json_decode($result, true);
		if (!$arr || !is_array($arr)) {
			throw new Exception('destroy gsid fail :'. $result);
		}
		if ($arr['errno'] != 0) {
			throw new Exception($arr['error'], $arr['errno']);
		}
		return true;
	}

	/**
	 * @param $gsid
	 * @return array
	 * @throws Exception
	 */
	public static function local_validate($gsid) {
		$arr_gsid_info = Sso_Sdk_Cookie_Gsid::parse($gsid);
		if ($arr_gsid_info == false || !isset($arr_gsid_info['uid'])) {
			throw new Exception("gsid parse fail: $gsid");
		}
		return array('uid' => $arr_gsid_info['uid']);
	}
}