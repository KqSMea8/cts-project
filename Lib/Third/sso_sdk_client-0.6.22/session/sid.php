<?php
/**
 * sid
 */

class Sso_Sdk_Session_Sid {
	const COOKIE_NAME = "SUB";

	/**
	 * sid在sub中的meta信息，sid的其他存储信息定义在 Sso_Sdk_Config::instance()->get('data.sid.data_type_map'); 中
	 * @var array
	 */
	private $_storage = array(
		'ctime'         => null,
		'status'        => 0,
		'flag'          => 0,
	);
	private static $_not_allow_overwrite = array('uid', 'domain', 'rand', 'idc', 'ctime');
	public function __construct($uid, $domain, $rand, $idc, $ctime){
		if (!Sso_Sdk_Tools_Domain::name2code($domain)) {
			throw new Exception("domain error [ $domain ]");
		}
		$arr = Sso_Sdk_Config::instance()->get('data.sid.data_type_map');
		foreach($arr as $key=>$item) {
			$this->_storage[$key] = isset($item['default'])?$item['default']:null;
		}
		$this->_set('uid', $uid);
		$this->_set('domain', $domain);
		$this->_set('idc', $idc);
		$this->_set('rand', $rand);
		$this->_set('ctime', $ctime);
	}

	public function set_data(array $arr) {
		foreach($arr as $k=>$v) {
			$this->set($k, $v);
		}
	}
	public function set($key, $val) {
		if (in_array($key, self::$_not_allow_overwrite)) return false; //有些内容不允许重写
		return $this->_set($key, $val);
	}
	private function _set($key, $val) {
		if (!array_key_exists($key, $this->_storage)) return false; //不存在的内容直接忽略
		$this->_storage[$key] = $val;
		return true;

	}
	public function get($key) {
		if (!array_key_exists($key, $this->_storage)) return null;
		return $this->_storage[$key];
	}
	public function get_data() {
		return $this->_storage;
	}
	public function is_store_enable() {
		if($this->get_status() == Sso_Sdk_Session_Session::STATUS_NORMAL && Sso_Sdk_Config::instance()->get('data.main.sub.enforce_normal_status_enable_store') === true) {
			return true;
		}
		return ($this->_storage['flag'] & Sso_Sdk_Session_Session::FLAG_STORE_ENABLE) === Sso_Sdk_Session_Session::FLAG_STORE_ENABLE;
	}
	
	public function is_expired() {
		$etime = $this->get_etime();
		return $etime !== null && $etime > 0 && $etime < time();
	}

	/**
	 * 是否需要更新sid
	 * @return bool
	 */
	public function is_need_renew() {
		if ($this->get_flag() & Sso_Sdk_Session_Session::FLAG_RENEW_DISABLE) {
			return false; //禁止renew
		}
		if (($arr_status = Sso_Sdk_Config::instance()->get('data.main.sub.no_need_renew_status')) && in_array($this->get_status(), $arr_status)) {
			return false; //不需要renew
		}
		$arr = Sso_Sdk_Config::instance()->get('data.main.renew_sid');
		if (($this->get_ctime() + $arr['after_ctime']) < time()) { // 1天前创建的需要更新
			return true;
		}
		$etime = $this->get_etime();
		if (isset($etime) && ((time() + $arr['before_etime']) > $etime)) {
			return true;
		}
		return false;
	}
	/**
	 * @return null
	 */
	public function get_ctime () {
		return $this->get('ctime');
	}

	/**
	 * @return mixed
	 */
	public function get_domain () {
		return $this->get('domain');
	}

	/**
	 * @return mixed
	 */
	public function get_etime () {
		return $this->get('etime');
	}

	/**
	 * @return int
	 */
	public function get_flag () {
		return $this->get('flag');
	}

	/**
	 * @return mixed
	 */
	public function get_from () {
		return $this->get('from');
	}

	/**
	 * @return mixed
	 */
	public function get_idc () {
		return $this->get('idc');
	}

	/**
	 * @return mixed
	 */
	public function get_logintype () {
		return $this->get('logintype');
	}

	/**
	 * @return mixed
	 */
	public function get_rand () {
		return $this->get('rand');
	}

	/**
	 * @return int
	 */
	public function get_status () {
		return $this->get('status');
	}

	/**
	 * @return mixed
	 */
	public function get_uid () {
		return $this->get('uid');
	}

	/**
	 * @return mixed
	 */
	public function get_username () {
		return $this->get('username');
	}
	// 检测flag是否设置的一些方法
	public function is_flag_invalid() {
		return $this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_INVALID);
	}
	public function is_flag_accurarcy() {
		return $this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_ACCURARCY);
	}
	public function is_flag_store_enable() {
		return $this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_STORE_ENABLE);
	}
	public function is_flag_read_only() {
		return $this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_READ_ONLY);
	}
	public function is_flag_renew_disable() {
		return $this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_RENEW_DISABLE);
	}
	public function is_flag_account_nonactivate() {
		return $this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_ACCOUNT_NONACTIVATE);
	}
	public function is_flag_account_nonactivate_expired() {
		return $this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_ACCOUNT_NONACTIVATE_EXPIRED);
	}

	public function is_flag_renew_enable() {
		return !$this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_RENEW_DISABLE);
	}
	public function is_flag_has_phone() {
		return $this->_is_set_flag(Sso_Sdk_Session_Session::FLAG_HAS_PHONE);
	}
	public function is_account_approved() {
		return $this->is_flag_has_phone();
	}
	/**
	 * 检查是否设置置顶标志位
	 * @param $flag
	 * @return bool
	 */
	private function _is_set_flag($flag) {
		return ($this->get_flag() & $flag) === $flag;
	}
	// ============== 关于sessionkey 的生成和解析 ============
	/**
	 * 该key用于保证用户内部的session的唯一性
	 * @param Sso_Sdk_Session_Sid $sid
	 * @return string
	 */
	public static function create_session_key_by_sid(Sso_Sdk_Session_Sid $sid) {
		return self::create_session_key($sid->get_rand(), $sid->get_domain(), $sid->get_ctime());
	}

	/**
	 * 该key用于保证用户内部的session的唯一性
	 * @param $rand
	 * @param $domain
	 * @param $ctime
	 * @return string
	 */
	public static function create_session_key($rand, $domain, $ctime) {
		$base62_relative_time = Sso_Sdk_Tools_Base62::encode($ctime - 1400083200);    //相对于 2014-05-15 00:00:00 的一个时间
		$domain_code = Sso_Sdk_Tools_Domain::name2code($domain);
		$base62_domain_low_byte = Sso_Sdk_Tools_Base62::encode($domain_code % 256);
		$base62_domain_hight_byte = Sso_Sdk_Tools_Base62::encode(($domain_code >> 8) % 256);
		return $rand.$base62_domain_hight_byte.$base62_domain_low_byte.$base62_relative_time;
	}

	/**
	 * 解析sessionkey
	 * @param $sessionkey
	 * @return array
	 */
	public static function parse_session_key($sessionkey) {
		$rand = substr($sessionkey, 0, 2);
		$base62_domain_hight_byte = $sessionkey{2};
		$base62_domain_low_byte = $sessionkey{3};
		$base62_relative_time = substr($sessionkey, 4);
		$domain_code = (Sso_Sdk_Tools_Base62::decode($base62_domain_hight_byte) << 8).  Sso_Sdk_Tools_Base62::decode($base62_domain_low_byte);
		$domain = Sso_Sdk_Tools_Domain::code2name($domain_code);
		$ctime = (int)Sso_Sdk_Tools_Base62::decode($base62_relative_time) + 1400083200;
		return array(
			'rand' => $rand,
			'domain'    => $domain,
			'ctime'     => $ctime,
		);
	}
	//===========================签名相关部分=============================
	//====错误常量====
	const E_PARSE_CONFIG        = 1001; //解析配置文件失败
	const E_SIGNED_NO_MATCH     = 1002; //签名错误
	const E_EXPIRED             = 1003; //过期
	const E_DOMAIN_NO_MATCH     = 1004; //域不匹配
	const E_PUBKEY_INVALID      = 1005; //按 Cookie 版本号取公钥失败
	const E_KEY_INVALID         = 1006; //按 Cookie 版本号取密钥失败

	//===== cookie 校验级别常量 ====
	const CHECK_LEVEL_NONE      = 0;    //不做任何校验
	const CHECK_LEVEL_DOMAIN    = 1;
	const CHECK_LEVEL_ALL       = -1;

	/**
	 * @param $sub
	 * @param $domain
	 * @param $check_level
	 * @return \Sso_Sdk_Session_Sid
	 * @throws Exception
	 */
	public static function validate($sub, $domain, $check_level) {
		try {
			$sub_info = self::_parse($sub, $domain);
		} catch(Exception $e) {
			throw $e;
		}
		self::_check($sub_info, $domain, $check_level); //这里不宜检查是否过期，因为过期的也有用

		$sid = new self($sub_info['uid'], $sub_info['domain'], $sub_info['rand'], $sub_info['idc'], $sub_info['ctime']);
		$sid->set_data($sub_info);
		return $sid;
	}

	private static function _check($sub_info, $domain, $cookie_check_level){
		//这里不检查过期，因为过期也是一种状态，需要返回信息的
		//检查域是否匹配
		if(($cookie_check_level & self::CHECK_LEVEL_DOMAIN) && isset($sub_info['domain'])) {
			$my_domain_code = Sso_Sdk_Tools_Domain::name2code($domain);
			$sub_domain_code = Sso_Sdk_Tools_Domain::name2code($sub_info['domain']);
			if ($my_domain_code && $sub_domain_code) {
				if (($my_domain_code & $sub_domain_code) != $my_domain_code) {
					throw new Exception("domain is not match : ".$sub_info['domain'], self::E_DOMAIN_NO_MATCH);
				}
			}
		}
	}

	public static function parse($sub) {
		$sub_info = self::_parse($sub);
		$sid = new self($sub_info['uid'], $sub_info['domain'], $sub_info['rand'], $sub_info['idc'], $sub_info['ctime']);
		$sid->set_data($sub_info);
		return $sid;
	}
	/**
	 * @param $sub
	 * @return array|mixed
	 * @throws Exception
	 */
	private static function _parse($sub) {
		$version = substr($sub, 0, 2);
		switch($version) {
			case '_2':
				return self::_parse_v2($sub);
			default:
				return self::_parse_v1($sub);
		}
	}

	/**
	 * @param $sub
	 * @return array|mixed
	 * @throws Exception
	 */
	private static function _parse_v2($sub) {

		$cookie_info = array();
		$sub = substr($sub, 2); //去掉cookie版本号
		$sub_text = Sso_Sdk_Tools_Base64::urlsafe_decode($sub);

		$sign = substr($sub_text, -20); // 截取后 64 字节，是数字签名
		$sub_text = substr($sub_text, 0, -20); // 把签名截掉

		// 取 cookie 版本 （第一个字节）
		$key_version_byte_1 = $sub_text[0];
		$key_version = ord($key_version_byte_1); // cookie 版本
		// 若版本不是当前有效版本，则 cookie 无效，验证失败。

		$cookie_info['version'] = '2';
		$arr_conf = self::_parse_config();
		// 校验数字签名
		$pub_key = isset($arr_conf["v$key_version"]['pub_key'])?$arr_conf["v$key_version"]['pub_key']:null; // 取签名公钥

		if(!$pub_key) { // 取公钥失败
			throw new Exception("pub key invalid for version: {$key_version}", self::E_PUBKEY_INVALID);
		}

		openssl_public_decrypt($sign, $digest, $pub_key);
		if($digest !== substr(md5($sub_text), 0, 9)) {
			throw new Exception('sign not match', self::E_SIGNED_NO_MATCH);
		}

		// 解密 cookie 信息
		$encrypt_sub_text = substr($sub_text, 1);
		$key = isset($arr_conf["v$key_version"]['key'])?$arr_conf["v$key_version"]['key']:null; // 取相应版本的秘钥
		if(!$key) { // 取密钥失败
			throw new Exception("key invalid for version: {$key_version}", self::E_KEY_INVALID);
		}
		$key = pack('H*', $key);
		if (!function_exists("openssl_decrypt") || ($plain_sub_text = @openssl_decrypt($encrypt_sub_text, 'rc4', $key, true)) === false) {
			// 函数不存在，或是不支持rc4算法，尝试纯PHP版本的实现
			$plain_sub_text = Sso_Sdk_Tools_Encrypt_RC4::decrypt($encrypt_sub_text, $key);
		}

		$offset = 0;

		// 校验 magic byte
		$magic_byte_1 = @$plain_sub_text[$offset++];
		if(ord($magic_byte_1) ^ 0xb1) {
			throw new Exception("magic error");
		}

		$cookie_info['ctime'] = Sso_Sdk_Tools_String::unpack(substr($plain_sub_text, $offset, 4), 'timestamp');
		$offset += 4;

		$cookie_info['status'] = Sso_Sdk_Tools_String::unpack($plain_sub_text[$offset], 'c1');
		$offset += 1;

		$cookie_info['flag'] = Sso_Sdk_Tools_String::unpack(substr($plain_sub_text, $offset, 2), 'c2');
		$offset += 2;

		$arr_data = Sso_Sdk_Config::instance()->get('data.sid.data_type_map');
		foreach($arr_data as $k=>$v) {
			$_len = ord($plain_sub_text[$offset++]);
			if ($_len == 0) continue;
			$val = substr($plain_sub_text, $offset, $_len);
			if (isset($v['pack'])) {
				$val = Sso_Sdk_Tools_String::unpack($val, $v['pack']);
			}
			$cookie_info[$k] = $val;
			$offset += $_len;
		}
		$cookie_info = self::_data_decode($cookie_info);
		return $cookie_info;
	}
	/**
	 * @param $sub
	 * @return array|mixed
	 * @throws Exception
	 */
	private static function _parse_v1($sub) {
		$cookie_info = array();
		
		$sub = str_replace(' ', '+', trim(urldecode($sub)));
		$sub_text = base64_decode($sub);

		$sign = substr($sub_text, -64); // 截取后 64 字节，是数字签名
		$sub_text = substr($sub_text, 0, -64); // 把签名截掉

		// 取 cookie 版本 （第一个字节）
		$version_byte_1 = $sub_text[0];
		$version = ord($version_byte_1); // cookie 版本
		// 若版本不是当前有效版本，则 cookie 无效，验证失败。

		$cookie_info['version'] = $version;
		$arr_conf = self::_parse_config();
		// 校验数字签名
		$pub_key = isset($arr_conf["v$version"]['pub_key'])?$arr_conf["v$version"]['pub_key']:null; // 取签名公钥

		if(!$pub_key) {
			// 取公钥失败
			throw new Exception("pub key invalid for version: {$version}", self::E_PUBKEY_INVALID);
		}

		openssl_public_decrypt($sign, $digest, $pub_key);
		if($digest !== md5($sub_text)) {
			throw new Exception('sign not match', self::E_SIGNED_NO_MATCH);
		}

		// 解密 cookie 信息
		$encrypt_sub_text = substr($sub_text, 1);
		$key = isset($arr_conf["v$version"]['key'])?$arr_conf["v$version"]['key']:null; // 取相应版本的秘钥
		$key = pack('H*', $key);
		$plain_sub_text = mcrypt_decrypt('rijndael-128', $key, $encrypt_sub_text, 'ecb', null);

		// 去掉对齐补位。aes 加密基于块，数据必须对齐到块大小（我们这里是 16 字节）。
		// 为了还原原信息，必须记录下对齐时补了多少字节。我们采取的办法是就用一个 8
		// 位整数作为填充字节，这个整数值就是对齐时补的字节数。于是有下面的逻辑。
		$pad = ord($plain_sub_text[strlen($plain_sub_text) - 1]);
		$plain_sub_text = substr($plain_sub_text, 0, strlen($plain_sub_text) - $pad);

		$offset = 0;

		// 校验 magic byte
		$magic_byte_1 = @$plain_sub_text[$offset++];
		if(ord($magic_byte_1) ^ 0xb1) {
			throw new Exception("magic error");
		}

		$cookie_info['ctime'] = Sso_Sdk_Tools_String::unpack(substr($plain_sub_text, $offset, 4), 'timestamp');
		$offset += 4;

		// 取“亚”登录状态。8 位整数标识各种“亚”登录状态，1、2、3、......具体那个数是哪种，后续通知。
		$cookie_info['status'] = Sso_Sdk_Tools_String::unpack($plain_sub_text[$offset], 'c1');
		$offset += 1;

		// 取 cookie 标志位。这里有两字节，16 位。每一位标识一种状态的“开/关”。含义未定，后续通知。
		$cookie_info['flag'] = Sso_Sdk_Tools_String::unpack(substr($plain_sub_text, $offset, 2), 'c2');
		$offset += 2;

		$arr_data = Sso_Sdk_Config::instance()->get('data.sid.data_type_map');
		foreach($arr_data as $k=>$v) {
			$_len = ord($plain_sub_text[$offset++]);
			if ($_len == 0) continue;
			$val = substr($plain_sub_text, $offset, $_len);
			if (isset($v['pack'])) {
				$val = Sso_Sdk_Tools_String::unpack($val, $v['pack']);
			}
			$cookie_info[$k] = $val;
			$offset += $_len;
		}
		$cookie_info = self::_data_decode($cookie_info);
		return $cookie_info;
	}
	/**
	 * parse cookie config file.
	 * @throws Exception
	 * @return bool
	 */
	private static function _parse_config() {

		// 取 Cookie （密钥）配置
		$arr_conf = Sso_Sdk_Config::instance()->get('data.key.sub');
		if(!$arr_conf) {
			throw new Exception("parse conf fail", self::E_PARSE_CONFIG);
		}
		return $arr_conf;
	}
	/**
	 * 对部分数据做解码; 这里没有使用foreach + switch 也是为了提高效率
	 * @param $arr
	 * @return mixed
	 */
	private static function _data_decode($arr) {
		if (isset($arr['domain'])) $arr['domain'] = Sso_Sdk_Tools_Domain::code2name($arr['domain']);
		if (isset($arr['idc'])) $arr['idc'] = Sso_Sdk_Tools_IDC::code2name($arr['idc']);
		if (isset($arr['wbversion'])) {
			$wbversionArr = unpack('C2', $arr['wbversion']);
			$unpack1 = array_shift($wbversionArr);
			$unpack2 = array_shift($wbversionArr);
			$arr['wbversion'] = $unpack1 . '.' . ($unpack2 >> 4) . '.' . ($unpack2 & 15);
		}

		return $arr;
	}
}
