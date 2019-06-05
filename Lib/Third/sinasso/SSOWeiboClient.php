<?PHP
/**
 * Sina sso client
 * @package  SSOClient
 * @filename SSOClient.php
 * @author   lijunjie <junjie2@staff.sina.com.cn>
 * @date 	 2010-3-8 15:00:00
 * @version  2.0
 */

include_once('json.php');
include_once('SSOBase.php');

header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
class SSOClient extends SSOBase{
	private $cookie		= '';

	private $loginType	= ''; // cookie | st
	private $returnType = 'META';

	private $uid = '';
	private $userInfo = '';
	private $_arrCookie;
	private $_arrLoginQuery = array();

	private $_serviceId	= SSOConfig::SERVICE;	// 应用产品ID
	private $_entry		= SSOConfig::ENTRY;		// 应用产品entry 和 pin , 获取用户详细信息使用，由统一注册颁发的
	private $_pin		= SSOConfig::PIN;

	const E_SYSTEM	= 9999; // 系统错误，用户不需要知道错误的原因

	const LOGIN_URL		= 'https://login.sina.com.cn/sso/login.php';	//登录接口
	const VALIDATE_URL 	= 'http://ilogin.sina.com.cn/sso/validate.php';	//验证接口
	const GETSSO_URL 	= 'http://ilogin.sina.com.cn/api/getsso.php';	//获取用户详细信息接口

	public function __construct() {
		$this->_arrCookie = $_COOKIE;
		$this->cookie = new SSOCookie();
	}

	public function logout() {
		$this->cookie->delCookie();
		// 下面这两个删除cookie主要是针对非sina.com.cn域写的，对sina.com.cn域没有影响
		setcookie('SSOLoginState', 'deleted', 1, '/', SSOConfig::COOKIE_DOMAIN);
		setcookie('ALF', 'deleted', 1, '/', SSOConfig::COOKIE_DOMAIN);
		//清除微博域自动登录cookie
		setcookie(SSOCookie::COOKIE_ALC, 'deleted', 1, '/', SSOConfig::COOKIE_DOMAIN);
	}
	
	public function setCustomCookie($arrCookie) {
		if (!$this->cookie->setCustomCookie($arrCookie)) {
			$this->_setError($this->cookie->getError(), $this->cookie->getErrno());
			return false;
		}
		$this->_arrCookie = array_merge($this->_arrCookie, $arrCookie);
		return true;
	}
	/**
	 * @brief check logined or not
	 * @param $noRedirect 是否允许在需要的时候访问sso，js判断用户登陆状态时，该参数可以设置为1，然后js自己去访问sso，避免使用iframe时浏览器兼容问题
	 */
	public function isLogined($noRedirect = 0) {
		// 为了避免rewrite丢掉url的参数，这里从$_SERVER['REQUEST_URI'] 中分析参数
		$arrQuery = $matches = array();
		if (preg_match('/\?(.*)$/',$_SERVER['REQUEST_URI'],$matches)) {
			parse_str($matches[1],$arrQuery);
		}
		$arrQuery = array_merge($arrQuery, $_POST);

		if (SSOConfig::USE_SERVICE_TICKET && isset($arrQuery['ticket'])) {	//不管验证成功与否，都直接返回，不能再请求SSO Server ，否则就死循环了
			if (!$this->isValidateST($arrQuery['ticket'])) {
				//对于非sina.com.cn域，需要删除SSOLoginState标志
				if (isset($this->_arrCookie['SSOLoginState'])) {
					$this->logout();  // 删除可能存在的用户身份
				}
				return false;
			}

			$this->loginType = 'st';
			//如果此时有ticket但无loginState则将来需要设置loginstate,针对非sina.com.cn域
			if (!isset($this->_arrCookie[SSOCookie::COOKIE_STATE])) {
				setcookie('SSOLoginState', time(), 0, '/', SSOConfig::COOKIE_DOMAIN);
			}
			return true;
		}
		
		//是否使用微博自动登录
		$use_weibo_alc	= defined('SSOConfig::USE_WEIBO_ALC') ? SSOConfig::USE_WEIBO_ALC : false;
		
		//SUE/SUP验证，补种WEIBOALC
		if (isset($this->_arrCookie[SSOCookie::COOKIE_SUE]) && isset($this->_arrCookie[SSOCookie::COOKIE_SUP])) {
			//check by cookie
			$userinfo = array();
			if ($this->cookie->getCookie($userinfo)) {
				$this->userInfo = $userinfo;
				$this->uid		= $this->userInfo['uniqueid'];
				$this->loginType= 'cookie';
				//补种WEIBOALC
				if ($use_weibo_alc && isset($this->_arrCookie[SSOCookie::COOKIE_ALF]) && !isset($this->_arrCookie[SSOCookie::COOKIE_ALC])) {
                    $this->cookie->setALCCookie($userinfo['uniqueid'], $this->_entry, $this->_pin);
                }
				return true;
			}
			// 无效的cookie试图删除
			$this->cookie->delCookie();
		}
		
		//WEIBOALC验证
		if ($use_weibo_alc && isset($this->_arrCookie[SSOCookie::COOKIE_ALF]) && isset($this->_arrCookie[SSOCookie::COOKIE_ALC])) {
			$userinfo = $this->cookie->autoLogin($this->_entry);
			if ($userinfo) {
				$this->userInfo = $userinfo;
				$this->uid		= $userinfo['uniqueid'];
				$this->loginType= 'alc';
				return true;
			}
        }

		// 这个判断必须出现在判断SSOLoginState、ALF之前，否则就死循环了
		if (@$arrQuery['retcode'] != 0) {
			$this->_setError(@$arrQuery['reason'], @$arrQuery['retcode']);
			$this->logout();  // 对于外域也一定要logout
			return false;
		}

		//only redirect to sso server when SSOLoginState or ALF is set
		if (isset($this->_arrCookie[SSOCookie::COOKIE_STATE]) || isset($this->_arrCookie[SSOCookie::COOKIE_ALF])) {
			if ($noRedirect) return true; // 为了方便js判断用户状态

			//redirect to sso server ,then user will send a new request with ST
			$urlPrefix = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
			$urlPrefix = $urlPrefix."://".$_SERVER['HTTP_HOST'];
			$returnURL = $urlPrefix.$_SERVER['REQUEST_URI'];

			$sslEnabled = true;
			if (isset($this->_arrCookie['SUR']) && $this->_arrCookie['SUR']) {
				parse_str($this->_arrCookie['SUR'],$arrSUR);
				if (isset($arrSUR['ssl']) && $arrSUR['ssl'] == 0) {
					$sslEnabled = false;
				}
			}
			$loginURL = $sslEnabled ? self::LOGIN_URL : str_replace("https:","http:",self::LOGIN_URL);
			
			$query = array(
				'url'		=> $returnURL,
				'_rand'		=> microtime(1), //防IE Cache
				'gateway'	=> 1,
				'service'	=> $this->_serviceId,
				'useticket'	=> SSOConfig::USE_SERVICE_TICKET ? 1 : 0,
				'returntype'=> $this->returnType,
			);
			$query = array_merge($query, $this->_arrLoginQuery);
			$url = $loginURL.'?'. http_build_query($query);
			header("Location: $url");
			exit();
		}

		if (SSOConfig::USE_SERVICE_TICKET && isset($arrQuery['retcode']) && $arrQuery['retcode'] != 0) {
			// 对于外域才参考retcode
			$this->_setError($arrQuery['reason'], $arrQuery['retcode']);
			$this->logout();  // 对于外域也一定要logout
			return false;
		}

		if (isset($arrQuery['reason'])) {
			// 对于
			$this->_setError($arrQuery['reason'], $arrQuery['retcode']);
			$this->logout();
			return false;
		}

		return false;
	}

	/**
	 * 获取用户详细信息,必须保证用户已登录或指定$uid 参数
	 */
	public function getUserInfoByUniqueid($uid) {
		$m = md5($uid . 0 .$this->_pin);
		$url = self::GETSSO_URL. "?user=".$uid."&ag=0&entry=".$this->_entry."&m=$m";

		$ret = @file_get_contents($url);

		if($ret === false){
			$this->_setError('call '.$url.' error', self::E_SYSTEM);
			return false;
		}
		parse_str($ret,$arr);
		if($arr['result'] != 'succ'){
			$this->_setError('call ' .$url ." error \n".$ret."\n".$arr['reason'], self::E_SYSTEM);
			return false;
		}
		return $arr;
	}
	/**
	 * 获取用户信息
	 */
	public function getUserInfo() {
		return $this->userInfo;
	}
	/**
	 * 获取登录方式
	 */
	public function getLoginType() {
		return $this->loginType;
	}
	/**
	 * 获取用户唯一ID
	 */
	public function getUniqueid() {
		return $this->uid;
	}
	/**
	 * 允许给login.php 自定义参数
	 */
	public function setLoginQuery($arrQuery) {
		$this->_arrLoginQuery = $arrQuery;
		return true;
	}
	/**
	 * 设置检查是否登录时的返回值类型
	 */
	public function setReturntype($returntype){
		$this->returnType = $returntype;
	}
	/**
	 * 检查ST是否有效,成功则通过$uid返回用户唯一ID
	 */
	private function isValidateST($ticket) {
		//	登录成功后分发到的ST再到SSO服务器端确认
		$url = self::VALIDATE_URL.'?' . 'service=' .urlencode($this->_serviceId) . '&ticket='.urlencode($ticket);
		$ret = @file_get_contents($url);
		if($ret === false){
			$this->_setError('call ' .$url. 'error', self::E_SYSTEM);
			return false;
		}
		$json = new Services_JSON();
		$obj = $json->decode($ret);

		if($obj->retcode != 0) {
			$this->_setError('call ' .$url ." error \n".$ret, self::E_SYSTEM);
			return false;
		}
		$this->uid = $obj->uid;
		return true;
	}
}
