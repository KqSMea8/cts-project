<?php
/**
 * 检查用户是否已登录
 */

class Sso_Sdk_Client {

	private static $_instance;

	/** @var  Sso_Sdk_Session_Session */
	private $session;

	/** @var  string */
	private $new_sid;

	/** @var  string 校验方式*/
	private $_check_type;

	/** @var \Sso_Sdk_User  */
	private $user;

    /** @var array */
    private $signs_array = array(
        'pc_web' => 1,
        'pc_client' => 2,
        'mobile_web' => 3,
        'mobile_app' => 4,
        'moblie_webbiew' => 5,
        'temp' => 6,
    );

	public static function instance() {
		if (!self::$_instance) {
			$instance = self::$_instance = new self();
			// 下面这些初始化操作不能放在构造函数中，因为一旦抛出异常，则单示例功能将失效
			//检查是否做了配置
			if (!Sso_Sdk_Config::is_init_already()) {
				throw new Exception('please init config use Sso_Sdk_Config::set_user_config(...)');
			}
			$instance->user = new Sso_Sdk_User();
			$instance->_check_login_status();
			if($instance->session) $instance->user->init($instance->session);
		}
		return self::$_instance;
	}
	private function __construct(){}
	
	/**
	 * 如果需要连续多次调用该SDK，且配置不同，先调用该方法，否则配置项不生效
	 * 如果配置相同，不要调用该方法！
	 */
    public static function unset_instance(){
        self::$_instance = null;
    }
	/**
	 * 获取sdk版本号
	 * @return string
	 */
	public static function get_version() {
		return '0.6.22';
	}

	/**
	 * 获取 ua 。
	 *
	 * @return string
	 */
	public static function get_ua() {
		return 'php-sso_sdk_client-' . self::get_version();
	}

	/**
	 * 返回用户登录级别, 非特殊情况，不建议直接使用该方法
	 * @return int|null
	 * @throws Exception
	 */
	private function _check_login_status() {
		Sso_Sdk_Tools_Log::instance()->heartbeatlog_by_version_conf();   //对于指定版本每次请求都发送心跳日志
		Sso_Sdk_Tools_Debugger::info(__CLASS__.' '.self::get_version(), 'sdk version'); //调试信息
		Sso_Sdk_Tools_Debugger::info(Sso_Sdk_Config::get_localstorage()->get_storage_uri(), 'config cache uri');
		$timer = null;
		try{
			$timer = Sso_Sdk_Tools_Timer::rand_start('check_login_status'); //随机采样检查登录状态花费的时间
			$status = $this->_get_login_level();
			Sso_Sdk_Tools_Timer::stop($timer);
		} catch (Exception $e){
			Sso_Sdk_Tools_Timer::stop($timer);
			throw $e;
		}
		if ($this->session) {
			Sso_Sdk_Tools_Log::instance()->accesslog($this->session->get_uid());
		}

		//校验用户其他病态状况
		if ($this->session) {
			$this->_verify_more($this->session);
		}
		if (is_null($status) && Sso_Sdk_Config::get_user_config('auto_create_visitor') === true) {
			$this->create_visitor();
		}
		return $status;
	}


	/**
	 * 返回用户登录级别
	 * @return int|null
	 * @throws Exception
	 */
	private function _get_login_level() {
		$domain = Sso_Sdk_Config::get_user_config('domain');
		$idc    = Sso_Sdk_Config::get_user_config('idc');

		$authinfo = $this->_parse_authinfo();
		$ticket = $authinfo['ticket'];
		$sub    = $authinfo['sub'];
		$gsid   = $authinfo['gsid'];


		$status = null;

		$useticket = Sso_Sdk_Config::get_user_config('useticket');
		$arr_query = $this->_get_query_array();
		if($useticket && isset($ticket)) { //验证票据
			$this->_check_type = 'ticket';
			try{
				$arr_result = Sso_Sdk_Session_Ticket::validate($arr_query['ticket']);
				if(isset($arr_result['uid']) && !isset($arr_result['sub'])){
					$this->session = new Sso_Sdk_Session_Session(new Sso_Sdk_Session_Sid($arr_result['uid'], $domain, '', $idc, time() ));
				} elseif (isset($arr_result['sub'])) {
					$sid = Sso_Sdk_Session_Sid::parse($arr_result['sub']);
					$this->session = new Sso_Sdk_Session_Session($sid);
				}
				if (isset($arr_result['cookie'])) {
					$arr_cookie = explode("\n", $arr_result['cookie']);
					foreach($arr_cookie as $line) {
						@header($line, false);
					}
				}
				if (isset($arr_query['savestate'])) {
					Sso_Sdk_Tools_Cookie::set_by_header('ALF', $arr_query['savestate'], $arr_query['savestate'],'/', $domain);
				}

				//如果支持安全设备，那么对安全设备进行校验
				$need_safe_device = Sso_Sdk_Config::get_user_config('need_safe_device');
				if( $need_safe_device && isset($arr_result['sub'])) {
					try{
						$arr_renew = array();
						$session = Sso_Sdk_Session_Session::validate ($arr_result['sub'], $arr_renew);
						$sdevflag = $session->get_sdevflag();
						$isopensafedev = $sdevflag & Sso_Sdk_Session_Session::SDEV_FLAG_ISOPENDEV; //是否开通安全设备
						$issafedev = $sdevflag & Sso_Sdk_Session_Session::SDEV_FLAG_ISSAFEDEV; //当前设备是否是安全设备
						$session->set_open_safe_device($isopensafedev);
						$session->set_device_safe_status($issafedev);
						$this->session = $session;
					}catch(Exception $e){
						if (isset($session)) {
							$session->set_open_safe_device(false);
							$session->set_device_safe_status(false);
						}
					}
				}
				return $this->session->get_status();
			} catch (Exception $e){
				// 这里不返回未登录，而是继续检查其他登录方式，好处在于不担心地址栏里面的ticket重复校验的问题了
				Sso_Sdk_Tools_Log::instance()->notice('validate st', array($ticket, $e->getMessage()));
				//继续其他登录方式的检查
				Sso_Sdk_Tools_Debugger::warn($e, 'validate st');
			}
		}
		if($sub) {//校验sub
			$this->_check_type = 'sub';
			try {
				$arr_renew = array();
				$session = Sso_Sdk_Session_Session::validate ($sub, $arr_renew);
				Sso_Sdk_Tools_Debugger::info('sub validate ok');
				$status = $session->get_status();
                $clienttype = $session->get_clienttype();
                $signs = array_search($clienttype, $this->signs_array);
                $sub_signs = Sso_Sdk_Config::get_user_config('sub_signs');
                if (!empty($sub_signs) && !in_array($signs, $sub_signs)) {
                    throw new Exception('signs error');
                }

				if (isset($arr_renew['sid'])) $this->new_sid = $arr_renew['sid'];
				if (isset($arr_renew['sub']) && Sso_Sdk_Config::get_user_config('autosetrenewcookie') === true) {
					Sso_Sdk_Tools_Cookie::set_by_header_string($arr_renew['sub']);
				}
				//正常状态过期的情况下，应尝试自动登录
				if ($status === Sso_Sdk_Session_Session::STATUS_NORMAL && $session->get_sid()->is_expired()) {
					throw new Exception('sub expired');
				}
				// 校验scf
				$scf_verify_flag = Sso_Sdk_Config::get_user_config ('scf_verify_flag');
				$scf_validate_ua = Sso_Sdk_Config::instance()->get("data.main.scf_validate_ua");
				$scf_validate_appkey = Sso_Sdk_Config::instance()->get("data.main.scf_validate_appkey");
				$scf_validate_disable = Sso_Sdk_Config::instance()->get("data.main.scf_validate_disable");//关闭可信设备的验证
				if (is_array($scf_validate_ua) && in_array($session->get_ua(), $scf_validate_ua)) {
					$scf_verify_flag = true;
				}
				if (is_array($scf_validate_appkey) && in_array($session->get('appkey'), $scf_validate_appkey)) {
					$scf_verify_flag = true;
				}
				$scf = Sso_Sdk_Tools_Request::cookie('SCF');
				if (!$scf_validate_disable && $scf && $scf_verify_flag){
					try{
						$isCredible = Sso_Sdk_Session_Http::instance()->is_credible($scf, $sub);
						$session->set_session_credible($isCredible);
					}catch (Exception $e){
						$session->set_session_credible(false);
					}
				}

				//校验安全设备
				$need_safe_device = Sso_Sdk_Config::get_user_config('need_safe_device');
				if($need_safe_device){
					try{
						$sdevflag = $session->get_sdevflag();
						$isopensafedev = $sdevflag & Sso_Sdk_Session_Session::SDEV_FLAG_ISOPENDEV; //当前设备是否开启安全设备
						$issafedev = $sdevflag & Sso_Sdk_Session_Session::SDEV_FLAG_ISSAFEDEV; //当前设备是否是安全设备
						$session->set_open_safe_device($isopensafedev);
						$session->set_device_safe_status($issafedev);
					}catch(Exception $e){
						$session->set_open_safe_device(false);
						$session->set_device_safe_status(false);
					}
				}
				$this->session = $session;
				return $status;
			} catch (Exception $e) {    //继续其他登录方式的检查
				Sso_Sdk_Tools_Log::instance()->notice('check sub', array($sub, $e->getMessage()));
				Sso_Sdk_Tools_Debugger::warn($e, 'check sub');
				if (!$this->session) {
					self::_delete_cookie(Sso_Sdk_Session_Sid::COOKIE_NAME);
					self::_delete_cookie(Sso_Sdk_Cookie_SUBP::COOKIE_NAME);
				}
			}
		}
		if ($gsid){ //校验gsid
			$this->_check_type = 'gsid';
			try{
				$info = Sso_Sdk_Session_Gsid::validate($gsid, array(
					'proj'  => Sso_Sdk_Config::get_user_config('proj'),
					'ivalue'  => Sso_Sdk_Config::get_user_config('ivalue'),
				));
				if ($info['uid']) {
					$this->session = new Sso_Sdk_Session_Session(new Sso_Sdk_Session_Sid($info['uid'], $domain, '', $idc, time() ));

					if (Sso_Sdk_Config::instance()->get('data.main.log.check_gsid_succ_log') === true) {  // 记录gsid成功的日志，利于gsid的下线
						Sso_Sdk_Tools_Log::instance()->notice('check suesup succ', array($sub, $gsid));
					}
					return Sso_Sdk_Session_Session::STATUS_NORMAL;
				}
			} catch (Exception $e){ //继续其他登录方式的检查
				Sso_Sdk_Tools_Log::instance()->notice('check gsid', array($gsid, $e->getMessage()));
				Sso_Sdk_Tools_Debugger::warn($e, 'check gsid');
				self::_delete_cookie(Sso_Sdk_Session_Gsid::COOKIE_NAME);
			}
		}

		//检查是否需要自动登录
		if (!isset($arr_query['retcode']) && self::canbe_autologin()) {
			self::autologin();
		}

		if (isset($e)) throw $e;
		return $status;
	}

	/**
	 * 执行自动登录操作
	 */
	public static function autologin() {
		header("Cache-Control: no-cache, no-store");
		header("Location: ".self::get_autologin_url());
		exit;
	}

	/**
	 * 检查是否可以执行自动登录操作
	 * @return bool
	 */
	public static function canbe_autologin() {
		if(Sso_Sdk_Config::get_user_config('autologin') === false) return false;
		if(Sso_Sdk_Tools_Request::cookie('SSOLoginState') || Sso_Sdk_Tools_Request::cookie('ALF')) {
			return true;
		}
		return false;
	}

	/**
	 * 返回用户对象
	 * @return Sso_Sdk_User
	 */
	public function get_user() {
		return $this->user;
	}

	/**
	 * 获取需要更新的sid
	 * @return string
	 */
	public function get_new_sid() {
		return $this->new_sid;
	}
	/**
	 * 获取本次登录状态的校验方式
	 * @return string
	 */
	public function get_check_type() {
		return $this->_check_type;
	}

	/**
	 * 根据uid获取用户信息
	 * @param $uid
	 * @throws Exception
	 * @return mixed
	 */
	public static function get_userinfo_by_uid($uid) {
		return Sso_Sdk_User::get_userinfo_by_uid($uid);
	}

	/**
	 * 登出方法，跳转到通行证登出地址
	 * @param string $return_url 指定登出后的返回地址，不指定则默认为本次请求地址
	 * @return bool
	 */
	public static function logout($return_url = '') {
		return self::_logout(true, $return_url);
	}

	/**
	 * 登出方法，只销毁/删除本域的认证信息；非特殊情况下不建议使用
	 * @return bool
	 */
	public static function logout_no_crossdomain() {
		return self::_logout(false);
	}

	/**
	 * 获取自动登录地址
	 * @return string
	 */
	public static function get_autologin_url() {
		$return_url  = self::get_return_url();
		$login_url = Sso_Sdk_Config::instance()->get('data.res.http.login');

		$arr_query = array(
			'url'		=> $return_url,
			'_rand'		=> microtime(1), // 防IE Cache
			'gateway'	=> 1,
			'service'	=> Sso_Sdk_Config::get_user_config('service'),
			'entry'		=> Sso_Sdk_Config::get_user_config('entry'),
			'useticket'	=> Sso_Sdk_Config::get_user_config('useticket') ? 1 : 0,
			'returntype'=> Sso_Sdk_Config::get_user_config('returntype'),
			'sudaref'	=> Sso_Sdk_Config::get_user_config('sudaref'),
			'_client_version' => self::get_version(),
		);

		if (Sso_Sdk_Tools_Request::is_ajax()) {
			$arr_query['returntype'] = 'HEADER2';
		}
		$arr_query = array_merge($arr_query, Sso_Sdk_Config::get_user_config('autologin_query'));
		return $login_url.'?'. http_build_query($arr_query);
	}

	/**
	 * 获取回跳地址
	 *
	 * @return string 返回地址
	 */
	public static function get_return_url() {
		return Sso_Sdk_Tools_Util::get_request_url();
	}

	/**
	 * 获取SUBP cookie内容
	 */
	public static function get_subp_info() {
		try{
			return Sso_Sdk_Cookie_SUBP::parse(Sso_Sdk_Tools_Request::cookie(Sso_Sdk_Cookie_SUBP::COOKIE_NAME));
		} catch (Exception $e){
			return null;
		}
	}

	/**
	 * 创建访客账号。
	 *
	 * @return false or no return. 生成访客失败返回 false 。否则不返回。
	 */
	public static function create_visitor() {

		//这里允许服务器端强制关闭自动创建访客。
		if(Sso_Sdk_Config::instance()->get('data.main.visitor.auto_create') === false) {
			return false;
		}

		// 取访客生成跳跳转地址。
		if(!$url = Sso_Sdk_Config::instance()->get('data.res.http.create_visitor')) {
			return false;
		}

		// get 参数。
		$arr_query = array('entry'   => Sso_Sdk_Config::get_user_config('entry'),
						   'a'       => 'enter',
						   'url'     => self::get_return_url(),
						   'domain'  => Sso_Sdk_Config::instance()->get_user_config('domain'),
						   'sudaref'	=> Sso_Sdk_Config::get_user_config('sudaref'),
						   'ua'      => self::get_ua(),
						   '_rand'   => microtime(1),);
		Sso_Sdk_Tools_Response::redirect($url . '?' . http_build_query($arr_query));

		exit();
	}

	/**
	 * 解析sub； 注意：该方法仅用于解析sub，不用于判断是否登录
	 * @param string $sub
	 * @return null|Sso_Sdk_Session_Sid
	 * @throws Exception
	 */
	public static function parse_sub($sub = '') {
		try {
			if (!$sub) {
				$authinfo = self::_parse_authinfo();
				$sub = $authinfo['sub'];
			}
			if (!$sub) return null;
			$sid = Sso_Sdk_Session_Sid::validate($sub, Sso_Sdk_Config::get_user_config ('domain'), 0);
		} catch (Exception $e) {
			throw $e;
		}
		return $sid;
	}

	/**
	 * 校验service ticket
	 * @param $ticket string
	 * @param array $arr_extra 通过该参数添加一些附加的信息
	 * @throws Exception
	 * @return mixed
	 */
	public static function validate_st($ticket, array $arr_extra = array()) {
		try{
			return Sso_Sdk_Session_Ticket::validate($ticket, $arr_extra);
		} catch (Exception $e){
			throw $e;
		}
	}
	/**
	 * 分析认证信息
	 */
	private static function _parse_authinfo() {
		$authinfo = array(
			'ticket'    => null,
			'sub'       => null,
			'gsid'      => null,
		);
		$sub = Sso_Sdk_Tools_Request::cookie('SUB')?Sso_Sdk_Tools_Request::cookie('SUB'):Sso_Sdk_Tools_Request::request('SUB');
		$gsid = Sso_Sdk_Tools_Request::cookie(Sso_Sdk_Session_Gsid::COOKIE_NAME);
		if (!$gsid) $gsid = Sso_Sdk_Tools_Request::request('gsid');
		$arr_query = self::_get_query_array();
		$ticket = isset($arr_query['ticket'])?$arr_query['ticket']:null;

		$user_config_authinfo = Sso_Sdk_Config::get_user_config('authinfo');
		if (isset($user_config_authinfo['sub'])) $sub = $user_config_authinfo['sub'];
		if (isset($user_config_authinfo['gsid'])) $gsid = $user_config_authinfo['gsid'];
		if (isset($user_config_authinfo['ticket'])) $ticket = $user_config_authinfo['ticket'];

		if ($gsid) {
			if (strlen($gsid) > 80 || $gsid{0} == '_') { //这里的gsid其实是sub
				$sub = $gsid;
			} else {
				$authinfo['gsid'] = $gsid;
			}
		}
		$authinfo['sub'] = $sub;
		$authinfo['ticket'] = $ticket;

		$user_config_authinfo_ignore = Sso_Sdk_Config::get_user_config('authinfo_ignore');
		if (isset($user_config_authinfo_ignore['sub'])) $authinfo['sub'] = null;
		if (isset($user_config_authinfo_ignore['gsid'])) $authinfo['gsid'] = null;
		if (isset($user_config_authinfo_ignore['ticket'])) $authinfo['ticket'] = null;


		$arr_authinfo_disable = Sso_Sdk_Config::instance()->get('data.main.authinfo_disable');  // 方便下线认证信息，如： SUE/SUP
		if (is_array($arr_authinfo_disable)) {
			foreach($arr_authinfo_disable as $val) {
				$authinfo[$val] = null;
			}
		}

		return $authinfo;
	}

	/**
	 * 登出方法
	 * @param bool $alldomain 是否立即登出所有域，允许则页面直接跳转到通行证登出url，对于ajax请求，不立即登出所有域
	 * @param string $return_url 指定登出后的返回地址，不指定则默认为本次请求地址
	 * @return bool
	 */
	private static function _logout($alldomain = true, $return_url = '') {
		$authinfo = self::_parse_authinfo();
		// ============ 销毁需要销毁的东西 ============
		if (isset($authinfo['sub'])) {
			try{
				Sso_Sdk_Session_Session::destroy($authinfo['sub']);
			} catch (Exception $e){
				Sso_Sdk_Tools_Log::instance()->warn('logout', 'destroy sub fail');
			}
		}
		if (isset($authinfo['gsid'])) {
			try{
				Sso_Sdk_Session_Gsid::destroy($authinfo['gsid']);
			} catch (Exception $e){
				Sso_Sdk_Tools_Log::instance()->warn('logout', 'destroy gsid fail');
			}
		}

		// ============ 删除cookie =================
		$autosetlogoutcookie = Sso_Sdk_Config::get_user_config('autosetlogoutcookie');
		if ($autosetlogoutcookie) {
			self::_delete_cookie(Sso_Sdk_Session_Sid::COOKIE_NAME);
			self::_delete_cookie(Sso_Sdk_Cookie_SUBP::COOKIE_NAME);
			self::_delete_cookie(Sso_Sdk_Session_Gsid::COOKIE_NAME);
			self::_delete_cookie('ALF');
			self::_delete_cookie('SSOLoginState');
			self::_delete_cookie('SUE');
			self::_delete_cookie('SUP');
			self::_delete_cookie('SUS');
		}
		// ============ 需要的话自动跳转到logout地址 ============
		if (Sso_Sdk_Tools_Request::is_ajax()) { //ajax请求的情况下无法执行所有域的登出操作
			return true;
		}
		if ($alldomain) {
			if (!$return_url) {
				$return_url = self::get_return_url();
			}
			$logout_url = Sso_Sdk_Config::instance()->get('data.res.http.logout');
			$arr_query = array(
				'entry' => Sso_Sdk_Config::get_user_config('entry'),
				'r'     => $return_url,
			);

			Sso_Sdk_Tools_Response::redirect($logout_url.'?'. http_build_query($arr_query));
		}
		return true;
	}

	/**
	 * 对于有些用户需要更多校验
	 * @param Sso_Sdk_Session_Session $session
	 * @return bool
	 */
	private function _verify_more(Sso_Sdk_Session_Session $session) {
		$flag = $session->get_verify_flag();
		if ($flag === null || $flag === 0) return true; //不需要处理
		$force_redirect = false;
		//允许服务器端定义强制跳转的标识
		$force_redirect_flag = Sso_Sdk_Config::instance()->get('data.main.session.force_redirect_on_need_verify');
		if ($force_redirect_flag & $flag > 0) {
			$force_redirect = true;
		}
		$custom_verify_flag = Sso_Sdk_Config::get_user_config('custom_verify_flag');
		// custom_verify_flag 拟启用，使用 ignore_verify_flag
		$ignore_verify_flag = Sso_Sdk_Config::get_user_config('ignore_verify_flag');
		if ($ignore_verify_flag && is_array($ignore_verify_flag)) {
			$arr_flag_name = Sso_Sdk_Session_Session::get_verify_flag_name();
			foreach($ignore_verify_flag as $name) {
				if(isset($arr_flag_name[$name])) $custom_verify_flag |= $arr_flag_name[$name];
				if ($name == "all") $custom_verify_flag |= ~0;
			}
		}
		$custom_deal = false;
		if ($custom_verify_flag) {
			if (($custom_verify_flag & $flag) > 0) {
				$custom_deal = true;
			}
		}

		if ( $custom_deal === true && $force_redirect === false) return true; //走自定义处理

		$verify_sguide_url = Sso_Sdk_Config::instance()->get('data.res.http.verify_sguide');
		// 跳转到漫游保护验证页面
		$arr_query = array(
			'entry' => Sso_Sdk_Config::get_user_config('entry'),
			'r'     => $this->get_return_url(),
			'sudaref'	=> Sso_Sdk_Config::get_user_config('sudaref'),
		);
		Sso_Sdk_Tools_Debugger::info(array('actual'=>$flag, 'custom_mask'=>$custom_verify_flag), 'redirect reason');
		Sso_Sdk_Tools_Response::redirect($verify_sguide_url.'?'. http_build_query($arr_query));
		exit();
	}

	/**
	 * 为兼容SUE/SUP
	 * @param $vf
	 * @param $vt
	 * @return int
	 */
	private static function _vf_vt_to_verify_flag($vf, $vt) {
		$verify_flag = 0;
		if ($vf == 2 && $vt == 1) {
			$vf = 1;
			$vt = 4;
		}
		if ($vf > 0) {
			if ($vt == 1) $verify_flag = $verify_flag | Sso_Sdk_Session_Session::VERIFY_FLAG_NEED_VSN;
			if ($vt == 2) $verify_flag = $verify_flag | Sso_Sdk_Session_Session::VERIFY_FLAG_NEED_PINCODE;
			if ($vt == 3) $verify_flag = $verify_flag | Sso_Sdk_Session_Session::VERIFY_FLAG_NEED_CHANGE_PWD;
			if ($vt == 4) $verify_flag = $verify_flag | Sso_Sdk_Session_Session::VERIFY_FLAG_READ_ONLY;
		}
		return $verify_flag;
	}
	/**
	 * 获取登录后的返回信息
	 *
	 * @return array
	 */
	private static function _get_query_array() {
		$arr_query = array();
		$request_uri = Sso_Sdk_Tools_Request::server("REQUEST_URI");
		// 为了避免 rewrite 丢掉 url 的参数，这里从 $_SERVER['REQUEST_URI'] 中分析参数
		if (preg_match('/\?(.*)$/', $request_uri, $matches)) {
			@parse_str($matches[1], $arr_query);
		}
		$arr_query = array_merge((array)$arr_query, $_POST);
		{   //将来的sso的参数都添加'sso_'前缀，避免和应用的参数冲突
			if (isset($arr_query['sso_ticket'])) {
				$arr_query['ticket'] = $arr_query['sso_ticket'];
			}
			if (isset($arr_query['sso_retcode'])) {
				$arr_query['retcode'] = $arr_query['sso_retcode'];
			}
			if (isset($arr_query['sso_reason'])) {
				$arr_query['reason'] = $arr_query['sso_reason'];
			}
			if (isset($arr_query['ssosavestate']) || isset($arr_query['sso_savestate'])) {
				$arr_query['savestate'] = intval($arr_query['ssosavestate']?$arr_query['ssosavestate']:$arr_query['sso_savestate']);
			}
		}
		return $arr_query;
	}

	/**
	 * 删除cookie
	 * @param $name
	 */
	private static function _delete_cookie($name) {
		if(Sso_Sdk_Tools_Request::cookie($name)) {
			Sso_Sdk_Tools_Cookie::delete($name, '/', Sso_Sdk_Config::get_user_config ('domain'));
		}
	}
	/**
	 * 调试方法
	 */
	private static function _debugger() {
		if(Sso_Sdk_Tools_Request::server('HTTP_X_SSO_SDK_CLIENT_DEBUG') === 'on') {
			Sso_Sdk_Tools_Response::header('sscv', self::get_version());
		}
	}

	/**
	 * 该SDK的初始化方法
	 */
	public static function sdk_init(){
		static $added = false;
		if($added) return;
		$added = true;
		require_once(dirname(__FILE__). "/tools/autoload.php");

		Sso_Sdk_Autoload::init();
		Sso_Sdk_Config::init_user_config();
		self::_debugger();
	}

	/**
	 * 返回用户安全设备状态
	 */
	public function environment_safe_status(){

		if(!$this->session){ //如果取不到session，那么用户肯定不是登录态，直接返回0，这里是为了兼容非登录态下误调用这个接口
			return Sso_Sdk_Session_Session::SDEV_FLAG_NOLOGIN;
		}

		//获取当前安全设备信息
		$scf_verify_flag = Sso_Sdk_Config::get_user_config ('scf_verify_flag');
		if($scf_verify_flag){ //如果支持对设备是否可信进行校验，那么去取设备是否可信
			$scf_recredible = $this->session->get_session_credible(); //设备是否可信
		}else{
			$scf_recredible = false;
		}
		$is_open_safe_dev = $this->session->get_open_safe_device(); //是否开启安全设备
		$is_safe_dev = $this->session->get_device_safe_status(); //当前设备是否为钥匙
		$is_abnormaladdress_login = $this->session->is_loginaddress_abnormal(); //是否是异地登录

		//计算用户得分
		$env_safe_score = 0;
		/*  case 1 用户开启了锁 */
		if($is_open_safe_dev){
			if($is_safe_dev){ //使用安全设备登录(3)
				$env_safe_score = Sso_Sdk_Session_Session::OPEN_SAFE_DEV_LOCKLOGIN;
			}elseif($scf_recredible){ //使用可信设备登录(2)
				$env_safe_score = Sso_Sdk_Session_Session::OPEN_SAFE_DEV_CREDIBLE;
			}elseif(!$is_abnormaladdress_login){ //未使用安全设备在常用登录地登录(-1)
				$env_safe_score = Sso_Sdk_Session_Session::OPEN_SAFE_DEV_LOGIN;
			}elseif($is_abnormaladdress_login){ //未使用安全设备且异地登录(-3)
				$env_safe_score = Sso_Sdk_Session_Session::OPEN_SAFE_DEV_ABNORMAL;
			}
		}else{ /* case 2 用户没有开锁 */
			if($scf_recredible){ //可信设备登录(2)
				$env_safe_score = Sso_Sdk_Session_Session::CLOSE_SAFE_DEV_CREDIBLELOGIN;
			}elseif($is_safe_dev){ //使用安全设备登录，得分与可信设备相同(2)
				$env_safe_score = Sso_Sdk_Session_Session::CLOSE_SAFE_DEV_LOCKLOGIN;
			}elseif(!$is_abnormaladdress_login){ //正常登录(1)
				$env_safe_score = Sso_Sdk_Session_Session::CLOSE_SAFE_DEV_NORMAL;
			}elseif($is_abnormaladdress_login){ //异地登录(-2)
				$env_safe_score = Sso_Sdk_Session_Session::CLOSE_SAFE_DEV_ABNORMAL;
			}
		}

		return $env_safe_score;
	}

}
Sso_Sdk_Client::sdk_init();

