<?php
/**
 * session 管理
 * 1.提供session的一些静态方法
 * 2.返回session对象
 */

class Sso_Sdk_Session_Session {

	const E_VALIDATE_FAIL = 10300;
	// base64 版本号常量。
	const BASE64_VERSION_1 = 1;
	
	// 本地or远程验证常量
	CONST VALIDATE_LOCAL = 1;
	CONST VALIDATE_REMOTE = 0;
	
	//=====状态常量====
	const STATUS_NORMAL  = 0;   // 正常状态
	const STATUS_VISITOR = 20;   // 访客
	const STATUS_EXITED  = 30;   // 已退出
	const STATUS_WEAK    = 40;   // 弱登录状态,
	const STATUS_EXPIRED = 50;   // 已过期（没有明确的这种状态）

	//=====flag常量====
	const FLAG_INVALID                     = 1;   // SUB 是否有效
	const FLAG_ACCURARCY                   = 2;   // SUB 是否准确 如指纹精确找回
	const FLAG_STORE_ENABLE                = 4;   // SUB 是否有存储
	const FLAG_READ_ONLY                   = 8;   // (被盗）只读
	const FLAG_RENEW_DISABLE               = 16;  // 是否禁止renew
	const FLAG_ACCOUNT_NONACTIVATE         = 32;  // 账户没激活
	const FLAG_ACCOUNT_NONACTIVATE_EXPIRED = 64;  // 没激活用户已过30天期限
	const FLAG_HAS_PHONE                   = 128; // 账号是否有手机(注册/绑定/安全/联系等)

	//=====客户端类型常量======
	const CLIENT_TYPE_PC_WEB            = 1;
	const CLIENT_TYPE_PC_CLIENT         = 2;
	const CLIENT_TYPE_MOBILE_WEB        = 3;
	const CLIENT_TYPE_MOBILE_APP        = 4;
	const CLIENT_TYPE_MOBILE_WEBVIEW    = 5;
	const CLIENT_TYPE_TEMP              = 6;

	//===== verify_flag常量 =====
	const VERIFY_FLAG_NONE              = 0;
	const VERIFY_FLAG_NEED_VSN          = 1;
	const VERIFY_FLAG_NEED_PINCODE      = 2;
	const VERIFY_FLAG_NEED_CHANGE_PWD   = 4;
	const VERIFY_FLAG_READ_ONLY         = 8;

	//===== sdevflag常量 =====
	const SDEV_FLAG_NOLOGIN   = 0; // 非登录态调用环境可信度反馈接口
	const SDEV_FLAG_ISOPENDEV = 1; // 是否开通了安全设备
	const SDEV_FLAG_ISSAFEDEV = 2; // 是否为安全设备(这里的安全设备为广义的安全设备，PC端通过扫描二维码或者短信验证通过也认为是安全设备)
	const SDEV_FLAG_ISEXTEND  = 4; // 是否为继承而来
	
	//===== Abnormal Login Address Constant =====
	const ABNORMAL_LOGIN_ADDRESS            = 1;    //登录地异常
	const ABNORMAL_LOGIN_ADDRESS_CIP        = 2;    //CIP异常
	const ABNORMAL_LOGIN_ADDRESS_IP_BANNED  = 4;    //登录IP被封杀
	const ABNORMAL_LOGIN_ADDRESS_PROVINCE   = 8;    //省份异常
	const ABNORMAL_LOGIN_ADDRESS_HISTORY    = 16;   //历史养成登录地异常
	const ABNORMAL_LOGIN_ADDRESS_USER_SET   = 32;   //手动设置的登录地异常

	//===== 环境可信度反馈常量 =====
	const OPEN_SAFE_DEV_LOCKLOGIN		= 3;	//开启设备锁且用钥匙登录
	const OPEN_SAFE_DEV_CREDIBLE		= 2;	//开启设备锁且用可信设备登录
	const OPEN_SAFE_DEV_LOGIN			= -1;	//开启设备锁非钥匙登录
	const OPEN_SAFE_DEV_ABNORMAL		= -3;	//开启设备锁非钥匙异地登录
	const CLOSE_SAFE_DEV_LOCKLOGIN		= 2;	//关闭设备锁钥匙登录(之前开通了安全设备)
	const CLOSE_SAFE_DEV_CREDIBLELOGIN	= 2;	//关闭设备锁可信设备登录
	const CLOSE_SAFE_DEV_NORMAL			= 1;	//关闭设备锁正常登录
	const CLOSE_SAFE_DEV_ABNORMAL 		= -2;	//关闭设备锁异地登录

	
	const SECURITY_EXPIRE = 2592000; //秒，30 days
	private static $current_gen_base64_v = self::BASE64_VERSION_1;
	private $_storage = array(
		'uid'           => null,
		'rand'          => null,
	);
	private static $_arr_verify_flag = array(
		'need_vsn' => self::VERIFY_FLAG_NEED_VSN,
		'need_pincode' => self::VERIFY_FLAG_NEED_PINCODE,
		'need_change_pwd' => self::VERIFY_FLAG_NEED_CHANGE_PWD,
		'readonly' => self::VERIFY_FLAG_READ_ONLY,
	);
	private static $_not_allow_overwrite = array('uid', 'domain', 'rand', 'idc', 'ctime');

	private $_session_credible;

	private $_device_safe_status;

	private $_open_safe_device;

	private $_validate_local = self::VALIDATE_LOCAL;
	
	public function __construct(Sso_Sdk_Session_Sid $sid) {
		$storage_map = Sso_Sdk_Config::instance()->get('data.session.storage_map');
		foreach($storage_map as $key=>$item) {
			$this->_storage[$key] = isset($item['default'])?$item['default']:null;
		}
		$this->_set('uid', $sid->get_uid());
		$this->_set('domain', $sid->get_domain());
		$this->_set('idc', $sid->get_idc());
		$this->_set('rand', $sid->get_rand());
		$this->_set('ctime', $sid->get_ctime());

		$this->set_data($sid->get_data());
	}
	public function set($key, $val) {
		if (in_array($key, self::$_not_allow_overwrite)) return false; //有些内容不允许重写
		return $this->_set($key, $val);
	}

	public function get($key) {
		if (!array_key_exists($key, $this->_storage)) return null;
		return $this->_storage[$key];
	}

	public function set_data(array $arr) {
		foreach($arr as $k=>$v) {
			$this->set($k, $v);
		}
	}
	public function get_data() {
		return $this->_storage;
	}
	public function get_uid() {
		return $this->get('uid');
	}
	public function get_rand() {
		return $this->get('rand');
	}
	public function get_domain() {
		return $this->get('domain');
	}
	public function get_ctime() {
		return $this->get('ctime');
	}
	public function get_idc() {
		return $this->get('idc');
	}
	public function get_status() {
		return $this->get('status');
	}
	public function get_ip() {
		return $this->get('ip');
	}
	public function get_ua() {
		return $this->get('ua');
	}
	public function get_etime() {
		return $this->get('etime');
	}
	public function get_mid() {
		return $this->get('mid');
	}
	public function get_appid() {
		return $this->get('appid');
	}
	public function get_flag() {
		return $this->get('flag');
	}
	public function get_username() {
		return $this->get('username');
	}
	public function get_from() {
		return $this->get('from');
	}
	public function get_logintype() {
		return $this->get('logintype');
	}
	public function get_clienttype() {
		return $this->get('clienttype');
	}
	public function get_verify_flag() {
		return $this->get('verify_flag');
	}
	public function get_entry() {
		return $this->get('entry');
	}
	public function get_ac() {
		return $this->get('ac');
	}
	public function get_sdevflag(){
		return $this->get('sdevflag');
	}

	// 检测flag是否设置的一些方法
	public function is_flag_invalid() {
		return $this->_is_set_flag(self::FLAG_INVALID);
	}
	public function is_flag_accurarcy() {
		return $this->_is_set_flag(self::FLAG_ACCURARCY);
	}
	public function is_flag_store_enable() {
		return $this->_is_set_flag(self::FLAG_STORE_ENABLE);
	}
	public function is_flag_read_only() {
		return $this->_is_set_flag(self::FLAG_READ_ONLY);
	}
	public function is_flag_renew_disable() {
		return $this->_is_set_flag(self::FLAG_RENEW_DISABLE);
	}
	public function is_flag_account_nonactivate() {
		return $this->_is_set_flag(self::FLAG_ACCOUNT_NONACTIVATE);
	}
	public function is_flag_account_nonactivate_expired() {
		return $this->_is_set_flag(self::FLAG_ACCOUNT_NONACTIVATE_EXPIRED);
	}
	public function is_flag_has_phone() {
		return $this->_is_set_flag(self::FLAG_HAS_PHONE);
	}
	public function is_account_approved() {
		return $this->is_flag_has_phone();
	}

	/**
	 * 检查是否需要更多验证
	 * @return bool
	 */
	public function is_verify_flag_need_vsn() {
		return $this->_is_set_verify_flag(self::VERIFY_FLAG_NEED_VSN);
	}
	public function is_verify_flag_need_pincode() {
		return $this->_is_set_verify_flag(self::VERIFY_FLAG_NEED_PINCODE);
	}
	public function is_verify_flag_need_change_pwd() {
		return $this->_is_set_verify_flag(self::VERIFY_FLAG_NEED_CHANGE_PWD);
	}
	public function is_verify_flag_read_only() {
		return $this->_is_set_verify_flag(self::VERIFY_FLAG_READ_ONLY);
	}

	/**
	 * @return boolean true:  abnormal login
	 *         boolean false:  normal login
	 */
	public function is_loginaddress_abnormal(){
		return $this->_is_set_ac_flag(self::ABNORMAL_LOGIN_ADDRESS);
	}
	public function is_loginaddress_cip_abnormal(){
		return $this->_is_set_ac_flag(self::ABNORMAL_LOGIN_ADDRESS_CIP);
	}
	public function is_loginaddress_ip_banned(){
		return $this->_is_set_ac_flag(self::ABNORMAL_LOGIN_ADDRESS_IP_BANNED);
	}
	public function is_loginaddress_province_abnormal(){
		return $this->_is_set_ac_flag(self::ABNORMAL_LOGIN_ADDRESS_PROVINCE);
	}
	public function is_loginaddress_userset_abnormal(){
		return $this->_is_set_ac_flag(self::ABNORMAL_LOGIN_ADDRESS_USER_SET);
	}
	public function is_loginaddress_history_abnormal(){
		return $this->_is_set_ac_flag(self::ABNORMAL_LOGIN_ADDRESS_HISTORY);
	}
	
	//当前session是否/设置可信
	public function get_session_credible(){
		return $this->_session_credible;
	}
	public function set_session_credible($isCredible){
		$this->_session_credible = $isCredible;
	}

	//当前session对应设备是否/设置安全设备
	public function get_device_safe_status(){
		return $this->_device_safe_status;
	}

	public function set_device_safe_status($isSafe){
		$this->_device_safe_status = $isSafe;
	}

	//当前session对应设备是否/设置设备锁
	public function get_open_safe_device(){
		return $this->_open_safe_device;
	}

	public function set_open_safe_device($isopen){
		$this->_open_safe_device = $isopen;
	}
	
	public function is_validate_local(){
	    return $this->_validate_local ? true :false;
	}
	
	private function set_validate_remote(){
	    $this->_validate_local = self::VALIDATE_REMOTE;
	}

	
	/**
	 * 生成sid
	 * @return Sso_Sdk_Session_Sid
	 */
	public function get_sid() {
		$sid = new Sso_Sdk_Session_Sid($this->get_uid(), $this->get_domain(), $this->get_rand(), $this->get_idc(), $this->get_ctime());
		$sid->set_data($this->get_data());
		return $sid;
	}
	
	/**
	 * 安全验证结果
	 */
	public function is_sverified(){
		return $this->get('sverify') ? true : false ;
	}
	public function is_sverify_succ(){
		$sverify = $this->get('sverify');
		if ($sverify) {
			$sverifyArr = Sso_Sdk_Session_Security::unserialize($sverify);
			return ($sverifyArr['vtime'] > (time() - self::SECURITY_EXPIRE)) && ($sverifyArr['result'] & Sso_Sdk_Session_Security::VERIFY_RESULT_SUCC);
		}
		return false;
	}
	public function is_sverify_fail(){
		$sverify = $this->get('sverify');
		if ($sverify) {
			$sverifyArr = Sso_Sdk_Session_Security::unserialize($sverify);
			return ($sverifyArr['vtime'] > (time() - self::SECURITY_EXPIRE)) && ($sverifyArr['result'] & Sso_Sdk_Session_Security::VERIFY_RESULT_FAIL);
		}
		return false;
	}
	public function is_sverify_undefined(){
		$sverify = $this->get('sverify');
		if ($sverify) {
			$sverifyArr = Sso_Sdk_Session_Security::unserialize($sverify);
			return ($sverifyArr['vtime'] > (time() - self::SECURITY_EXPIRE)) && ($sverifyArr['result'] & Sso_Sdk_Session_Security::VERIFY_RESULT_UNDEFINED);
		}
		return false;
	}
	public function is_sverify_item_id(){
		$sverify = $this->get('sverify');
		if ($sverify) {
			$sverifyArr = Sso_Sdk_Session_Security::unserialize($sverify);
			return $sverifyArr['item'] & Sso_Sdk_Session_Security::VERIFY_ITEM_ID;
		}
		return false;
	}
	public function is_sverify_item_mobile(){
		$sverify = $this->get('sverify');
		if ($sverify) {
			$sverifyArr = Sso_Sdk_Session_Security::unserialize($sverify);
			return $sverifyArr['item'] & Sso_Sdk_Session_Security::VERIFY_ITEM_MOBILE;
		}
		return false;
	}
	public function get_sverify_vtime(){
		$sverify = $this->get('sverify');
		if ($sverify) {
			$sverifyArr = Sso_Sdk_Session_Security::unserialize($sverify);
			return $sverifyArr['vtime'];
		}
		return false;
	}

	/**
	 * 检查是否设置verify_flag某项标志位
	 * @param $flag
	 * @return bool
	 */
	private function _is_set_verify_flag($flag) {
		return ($this->_storage['verify_flag'] & $flag) === $flag;
	}
	/**
	 * 检查是否设置ac某项标志位
	 * @param $flag
	 * @return bool
	 */
	private function _is_set_ac_flag($flag) {
		return ($this->_storage['ac'] & $flag) === $flag;
	}
	/**
	 * 检查是否设置某项标志位
	 * @param $flag
	 * @return bool
	 */
	private function _is_set_flag($flag) {
		return ($this->_storage['flag'] & $flag) === $flag;
	}

	private function _set($key, $val) {
		if (!array_key_exists($key, $this->_storage)) return false; //不存在的内容直接忽略
		switch ($key){
			case 'ac': //ac=0/null表示登录地正常，不用保存
			case 'verify_flag':
				if ($val == 0)
					$val = null;
				break;
		}
		$this->_storage[$key] = $val;
		return true;
	}
	//=====================一些静态方法======================
	/**
	 * 返回所有可能的校验位
	 */
	public static function get_verify_flag_name() {
		return self::$_arr_verify_flag;
	}
	/**
	 * 根据配置信息决定如何校验session
	 * @param $sub
	 * @param array $arr_renew
	 * @throws Exception
	 * @return Sso_Sdk_Session_Session
	 */
	public static function validate($sub, &$arr_renew = array()) {
		/* 用于测试查看验证路径 */
         Sso_Sdk_Session_Tracker::reset_validate_path();

		// 是否在黑名单
		$in_blacklist = false;

		// 灰度黑名单
		$uids = Sso_Sdk_Config::instance()->get("data.main.session.unuse_v3_api_uids");
		if (!empty($uids) && is_array($uids)) {
			$sid = self::_local_validate($sub, 'local_validate');
			if (in_array($sid->get_uid(), $uids)) {
				Sso_Sdk_Tools_Debugger::info('use_v3_api: in_blacklist');
				$in_blacklist = true;
			}
		}

		// 不在黑名单,继续尝试灰度
		if (!$in_blacklist) {

			// 流量灰度
			if (self::use_v3_api()) {
				Sso_Sdk_Tools_Debugger::info('use_v3_api: random');
				Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::V3_STEP_16);
				return self::_v3_validate($sub, $arr_renew);
			}

			// 白名单灰度
			if (!isset($sid)) {
				$sid = self::_local_validate($sub, 'local_validate');
			}
			if (self::use_v3_api_uid($sid->get_uid())) {
				Sso_Sdk_Tools_Debugger::info('use_v3_api: in_whitelist');
				Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::V3_STEP_17);
				return self::_v3_validate($sub, $arr_renew);
			}
		}

		if (Sso_Sdk_Config::get_user_config('remote_validate') == false)  { //配置不开启远程校验，直接返回
			Sso_Sdk_Tools_Debugger::info('remote validate not enable');
            Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_1);
			return new Sso_Sdk_Session_Session($sid);
		}

		if ($sid->is_expired()) {       //过期就直接返回
			Sso_Sdk_Tools_Debugger::info('sid is expired');
            Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_2);
			return new Sso_Sdk_Session_Session($sid);
		}

		if (!$sid->is_store_enable() && !$sid->is_need_renew()) { //没有存储也不需要renew直接返回
			Sso_Sdk_Tools_Debugger::info('sid is not store enable');
            Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_3);
			return new Sso_Sdk_Session_Session($sid);
		}

		//有些状态的SUB没有必要服务器端校验，不需要校验则自然也不需要renew，直接返回
		$status = $sid->get_status();
		if (($arr_status = Sso_Sdk_Config::instance()->get('data.main.sub.no_remote_validate_status')) && in_array($status, $arr_status)) {
            Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_4);
			return new Sso_Sdk_Session_Session($sid);
		}

		$session = null;
		if ($sid->is_store_enable()) { //有存储总是先访问mc
			try{
                Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_5);
				$session = Sso_Sdk_Session_Memcache::instance()->validate($sid);
				Sso_Sdk_Tools_Debugger::info('validate by mc ok');
				
				$session->set_validate_remote();
			} catch(Exception $e) {
				Sso_Sdk_Tools_Debugger::warn($e);
				switch($e->getCode()) {
					case Sso_Sdk_Session_Memcache::E_CONFIG_EMPTY: //未配置cache，走接口校验
						break;
					case Sso_Sdk_Session_Memcache::E_NETWORK_EXCEPTION: //cache故障，如果不需要renew的话，就可以直接返回了
						Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_6);
						if (!$sid->is_need_renew()) return new Sso_Sdk_Session_Session($sid);
						break;
					case Sso_Sdk_Session_Memcache::E_NOT_FOUND: //cache中不存在,或数据可能有问题,继续走接口校验
						break;
					case Sso_Sdk_Session_Memcache::E_DELETED:
						Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_7);
						throw new Exception('sid invalid on deleted', self::E_VALIDATE_FAIL);
					default:
				}
			}
		}

		if ($session && !$sid->is_need_renew()) return $session; //如果cache访问成功，并且不需要renew，则可以直接返回了
		$allow_renew = Sso_Sdk_Config::get_user_config('allow_renew');
		if ($session && $allow_renew == false)  { //强制要求不能renew
			Sso_Sdk_Tools_Debugger::info('allow renew disabled');
			return $session;
		}
		$arr_user_validate_query = Sso_Sdk_Config::get_user_config('validatesid_query');
		try {
			Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_8);
			if ($allow_renew && $sid->is_need_renew() && isset($arr_user_validate_query['dinfo'])) {
				Sso_Sdk_Tools_Debugger::info('step into renew');
				Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_9);
				$arr = Sso_Sdk_Session_Http::instance()->renew($sub);
			} else {
				Sso_Sdk_Tools_Debugger::info('step into validate');
				Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_10);
				$arr = Sso_Sdk_Session_Http::instance()->validate($sub);
			}
			if (isset($arr['data']['sid'])) $arr_renew['sid'] = $arr['data']['sid'];
			if (isset($arr['data']['sub'])) $arr_renew['sub'] = $arr['data']['sub'];

			if (isset($arr['data']['sid'])){
				Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_11);
				$session = new Sso_Sdk_Session_Session(Sso_Sdk_Session_Sid::parse($arr['data']['sid']));
			}else{
				Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_12);
				$session = new Sso_Sdk_Session_Session($sid);
			}
			$session->set_data($arr['data']['session']);
			if ($sid->get_rand() != $session->get_rand() && $session->get_rand() != ''){
				Sso_Sdk_Tools_Debugger::info('renew ok');
			}
			$session->set_validate_remote();
			return $session;
		} catch(Exception $e) {
			switch($e->getCode()) {
				case Sso_Sdk_Session_Http::E_VALIDATE_INVALID:
					Sso_Sdk_Tools_Debugger::warn($e, "validate fail:");
					Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_13);
					throw new Exception('sid invalid on http', self::E_VALIDATE_FAIL);
				default: //其他错误直接放过
					Sso_Sdk_Tools_Debugger::warn($e, "validate fail but ignore:");
					break;
			}
		}
		Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::STEP_14);
		return new Sso_Sdk_Session_Session($sid);
	}

	/**
	 * 销毁session
	 * @param $sub
	 * @return array|bool
	 * @throws Exception
	 */
	public static function destroy($sub) {

		// 流量灰度
		if (self::use_v3_api()) {
			return self::_v3_destroy($sub);
		}

		try {
			$sid = Sso_Sdk_Session_Sid::parse ($sub);
		} catch (Exception $e) {
			return false;
		}

		// uid 灰度
		if (self::use_v3_api_uid($sid->get_uid())) {
			return self::_v3_destroy($sub);
		}

		if (!$sid->is_store_enable()) {
			//删cookie放在logout中来做
			return false;
		}
		// 删除session通过接口进行
		try {
			$ret =Sso_Sdk_Session_Http::instance()->destroy_by_sid ($sub);
		} catch (Exception $e) {
			throw $e;
		}
		return $ret;
	}

	/**
	 * 存储反序列化
	 *
	 * @param $str
	 * @throws Exception
	 * @return array
	 */
	public static function unserialize($str) {
		$version = ord($str{0});
		switch($version) {
			case 1:
				$arr = self::_unserialize_v1($str);
				break;
			default:
				throw new Exception("unserialize fail");
		}

		//编码转换
		$arr['domain'] = Sso_Sdk_Tools_Domain::code2name($arr['domain']);
		$arr['idc'] = Sso_Sdk_Tools_IDC::code2name($arr['idc']);
		//必选参数
		if (!$arr['domain'] || !$arr['ctime'] || !$arr['idc']) {
			throw new Exception("data is broken");
		}
		if (isset($arr['wbversion'])) {
		    $wbversionArr = unpack('C2', $arr['wbversion']);
		    $unpack1 = array_shift($wbversionArr);
		    $unpack2 = array_shift($wbversionArr);
		    $arr['wbversion'] = $unpack1 . '.' . ($unpack2 >> 4) . '.' . ($unpack2 & 15);
		}
		return $arr;

	}

	/**
	 * 存储返回序列化版本1
	 * @param $str
	 * @return array
	 */
	private static function _unserialize_v1($str)
	{
		$offset = 1;
		$len = strlen($str);
		$storage_map = Sso_Sdk_Config::instance()->get('data.session.storage_map');

		$map = array();
		foreach ($storage_map as $key => $item) {
			$map[$item['index']] = $key;
		}
		$arr = array();
		while ($offset < $len) {
			$key = ord($str{$offset});
			$value_len = ord($str{$offset + 1});
			if ($value_len == 0) {
				$offset += 2;
				continue;
			}
			$value = substr($str, $offset + 2, $value_len);
			$arr[$map[$key]] = $value;
			$offset = $offset + 2 + $value_len;
		}
		foreach ($arr as $key => $val) {
			if (isset($storage_map[$key]['pack'])) {
				$pack = $storage_map[$key]['pack'];
				if (strlen($val) == 1 && $pack == "c2") { // 兼容 1个字节的status存储成了2字节的问题
					$pack = "c1";
				}
				$arr[$key] = Sso_Sdk_Tools_String::unpack($val, $pack);
			}
			if(isset($storage_map[$key]['transform'])) {
			    switch ($storage_map[$key]['transform']) {
			        case 'tid':
			            $arr[$key] = self::reverse_transform_tid($val);
			            break;
			        default:
			            break;
			    }
			}
		}
		return $arr;
	}

	/**
	 * 获取 v3 版当前 idc 配置
	 *
	 * @return array
	 */
	public static function get_v3_idc_config() {
		$cfg = Sso_Sdk_Config::instance();
		$idc = $cfg->get_user_config('idc');
		$config = $cfg->get("data.main.session.use_v3_api_idc");
		if (isset($config[$idc])) {
			return $config[$idc];
		} else if (isset($config['default'])) {
			return $config['default'];
		} else {
			return array();
		}
	}

	/**
	 * 该流量是否灰度到 v3 版 api
	 *
	 * @return boolean
	 */
	public static function use_v3_api() {
		$config = self::get_v3_idc_config();
		if (!isset($config['prob']) || $config['prob'] == 0) {
			return false;
		}
		if (isset($config['max'])) {
			if ($config['prob'] <= $config['max']) {
				return (mt_rand(1, $config['max']) <= $config['prob']) ? true : false;
			}
		}
		return false;
	}

	/**
	 * 该用户是否使用 v3 版 api
	 *
	 * @param $uid
	 * @return bool
	 */
	public static function use_v3_api_uid($uid) {
		$cfg = Sso_Sdk_Config::instance();
		$uids = $cfg->get("data.main.session.use_v3_api_uids");
		return (is_array($uids) && in_array($uid, $uids));
	}

	/**
	 * @param $sub
	 * @param array $renew
	 * @return null|Sso_Sdk_Session_Session
	 * @throws Exception
	 */
	private static function _v3_validate($sub, &$renew = array()) {

		// 主动降级功能暂时保留
		if (Sso_Sdk_Config::get_user_config('remote_validate') == false)  {
			$sid = self::_local_validate($sub, 'v3_local_validate');
            Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::V3_STEP_20);
			return new Sso_Sdk_Session_Session($sid);
		}

		try {
			$session = null;
			$http = Sso_Sdk_Session_Http::instance();
			$allow_renew = Sso_Sdk_Config::get_user_config('allow_renew');
			$arr = $http->v3_validate($sub, $allow_renew);
			if (isset($arr['data']['sub'])) {
				$renew['sub'] = $arr['data']['sub'];
			}
			if (isset($arr['data']['sid'])) {
				// 替换新 sub
				$sub = $renew['sid'] = $arr['data']['sid'];
			}
			$sid = Sso_Sdk_Session_Sid::parse($sub);
            Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::V3_STEP_21);
			$session = new Sso_Sdk_Session_Session($sid);
			$session->set_data($arr['data']['session']);
			$session->set_validate_remote();
			return $session;
		} catch (Exception $e) {
			$message = $e->getCode().", ".$e->getMessage();
			switch($e->getCode()) {
				case Sso_Sdk_Session_Http::E_VALIDATE_INVALID:
                    Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::V3_STEP_22);
					Sso_Sdk_Tools_Debugger::warn($e, "v3_validate: validate fail:");
					throw new Exception('v3_validate: sid invalid on http', self::E_VALIDATE_FAIL);
				default: // 只处理 http 明确返回的验证失败错误, 其他错误全部放过做本地验证
					Sso_Sdk_Tools_Debugger::warn($e, "v3_validate: validate fail but ignore: ".$message);
					$sid = self::_local_validate($sub, 'v3_local_validate');
                    Sso_Sdk_Session_Tracker::update_validate_path(Sso_Sdk_Session_Tracker::V3_STEP_23);
					return new Sso_Sdk_Session_Session($sid);
			}
		}
	}

	/**
	 * @param $sub
	 * @return bool
	 * @throws Exception
	 */
	private static function _v3_destroy($sub) {
		$http = Sso_Sdk_Session_Http::instance();
		return $http->v3_destroy($sub);
	}

	/**
	 * 按级别做本地校验 sub,失败抛异常并回传日志
	 * @param $sub
	 * @param string $source
	 * @return Sso_Sdk_Session_Sid
	 * @throws Exception
	 */
	private static function _local_validate($sub, $source = 'validate') {
		try {
			$check_level = Sso_Sdk_Config::get_user_config('check_level');
			if ($check_level === -1) { // 说明没有设置过
				// 试图通过 check_domain 来替代 check_level 的设置 todo: 添加测试用例
				$check_domain = Sso_Sdk_Config::get_user_config('check_domain');
				if ($check_domain) {
					$check_level |= Sso_Sdk_Session_Sid::CHECK_LEVEL_DOMAIN;
				} else {
					$check_level &= ~Sso_Sdk_Session_Sid::CHECK_LEVEL_DOMAIN;
				}
			}
			return Sso_Sdk_Session_Sid::validate($sub, Sso_Sdk_Config::get_user_config('domain'), $check_level);
		} catch (Exception $e) {
			$message = $e->getCode().",".$e->getMessage();
			Sso_Sdk_Tools_Debugger::error('validate fail on signed');
			Sso_Sdk_Tools_Log::instance()->notice($source, $message);
			throw $e;
		}
	}

	/**
	 * @param $tid
	 * @return string
	 */
	static private function reverse_transform_tid($tid) {
	    return self::base64_encode($tid);
	}

	/**
	 * @param $tid
	 * @return string
	 */
	static public function base64_encode($tid) {
	    // 做 base64 编码。
	    switch(self::$current_gen_base64_v) {
	        case 1:
	        default:
	            $id = Sso_Sdk_Tools_Base64::sso_urlsafe_encode($tid);
	            break;
	    }
	    return str_pad(self::$current_gen_base64_v, 2, '0', STR_PAD_LEFT) . $id;
	}

}




