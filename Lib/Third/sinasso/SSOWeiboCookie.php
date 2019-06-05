<?php
/**
 * Sina sso client
 * @filename SSOWeiboCookie.php
 * @author   lijunjie <junjie2@staff.sina.com.cn>
 * @date 	 2010-04-14
 * @version  1.0
 */

class SSOCookie {
	const COOKIE_SUE		= "SUE";			//sina user encrypt info
	const COOKIE_SUP		= "SUP";			//sina user plain info
	const COOKIE_ALF		= "ALF";			//auto login flag
	const COOKIE_ALC		= "WEIBOALC";		//weibo auto login cookie
	const COOKIE_STATE		= "SSOLoginState";	//login state
	
	const COOKIE_EXPIRE		= 86400; //cookie 过期时间
	const COOKIE_PATH		= "/";
	const COOKIE_DOMAIN		= ".weibo.com";
	const COOKIE_KEY_FILE	= "/usr/local/sinasrv2/lib/php/cookie.conf";
	const COOKIE_VERSION	= 1;
	
	const GETALC_URL		= 'http://ilogin.sina.com.cn/api/getalc.php';	//获取用户自动登录cookie接口
	const CHKALC_URL		= 'http://ilogin.sina.com.cn/api/chkmini.php';	//验证自动登录cookie接口

	private $_time			= '';
	private $_error;
	private $_errno = 0;
	private $_arrConf; // the infomation in cookie.conf
	private $_arrCookieInfo = array("uid","user","ag",
									"email","nick","name",
									"sex","dob","ps"); // only for set cookie
	public function __construct($config = self::COOKIE_KEY_FILE) {
	    //$config = APPPATH.'/config/cookie.conf';
		$this->_time = time();
		if(!$this->_parseConfigFile($config)){
			throw new Exception($this->getError());
		}
	}
	public function getCookie(&$arrUserInfo) {
		$sup = $_COOKIE[self::COOKIE_SUP];
		if(!$sup) {
			$this->_setError("sup not exists");
			return false;
		}

		parse_str($sup,$arrSUP);
		$cv = $arrSUP["cv"];
		switch($cv) {
		case 1:
			return $this->_getCookieV1($arrUserInfo);
			break;
		default:
			return false;
		}
	}
	/**
	 * get cookie string for setting cookie
	 * @param $arrUserInfo : [ uid | user | ag | email | nick | name | sex | dob | ps]
	 */
	public function getCookieStr($arrUserInfo) {
		$arrUserInfo = $this->convertUserInfo($arrUserInfo);
		if (!isset($arrUserInfo["ps"])) {
			$arrUserInfo["ps"] = 0;
		}

		$arrConf = $this->_arrConf;
		$bt = $arrUserInfo["bt"]?$arrUserInfo["bt"]:$this->_time;
		$et = isset($arrUserInfo["et"])?$arrUserInfo["et"]:($this->_time + self::COOKIE_EXPIRE);

		// for SUP
		$arrSUP = array();
		$arrSUP["cv"] = self::COOKIE_VERSION;
		$arrSUP["bt"] = $bt;
		$arrSUP["et"] = $et;

		// convert encode for setcookie, cookie value should be urlencoded
		foreach ( $this->_arrCookieInfo as $val) {
			$arrSUP[$val] = iconv("GBK","UTF-8",$arrUserInfo[$val]);
		}
		$sup = $this->_raw_http_build_query($arrSUP);

		// for SUE
		$str= $bt. $et. $arrUserInfo["uniqueid"] . $arrUserInfo["userid"] . $arrUserInfo["appgroup"]. $arrConf[$arrConf['v']] ;
		$es = md5($str);
		$es2 = md5($this->_rawurlencode($sup) . $arrConf[$arrConf["v"]]);
		$arrSUE = array("es"=>$es,"es2"=>$es2, "ev"=>$this->_arrConf["v"]);
		$sue = $this->_raw_http_build_query($arrSUE);

		$sue = $this->_rawurlencode($sue);
		$sup = $this->_rawurlencode($sup);
		$cookieSUE = "Set-Cookie: SUE=$sue;path=".self::COOKIE_PATH.";domain=".self::COOKIE_DOMAIN.";Httponly";
		$cookieSUP = "Set-Cookie: SUP=$sup;path=".self::COOKIE_PATH.";domain=".self::COOKIE_DOMAIN;
		return $cookieSUE."\n".$cookieSUP;
	}
	/**
	 * @param $arrUserInfo : [ uid | user | ag | email | nick | name | sex | dob | ps]
	 */
	public function setCookie($arrUserInfo) {
		$str = $this->getCookieStr($arrUserInfo);
		if(!$str) {
			return false;
		}
		$header = explode("\n",$str);
		foreach($header as $line) {
			header($line,false);
		}
		return true;
	}
	/**
	 * update cookie
	 * @param $arrNewUserInfo : [ uid | user | ag | email | nick | name | sex | dob | ps]
	 */
	public function updCookie($arrNewUserInfo) {
		if (!$this->getCookie($arrUserInfo)) {
			return false;
		}
		foreach($arrNewUserInfo as $key=>$val) {
			$arrUserInfo[$key] = $val;
		}
		if (!$this->setCookie($arrUserInfo)) {
			return false;
		}
		return true;
	}

	// delete cookie
	public function delCookie() {
		setcookie(self::COOKIE_SUE,"deleted",1,self::COOKIE_PATH,self::COOKIE_DOMAIN);
		setcookie(self::COOKIE_SUP,"deleted",1,self::COOKIE_PATH,self::COOKIE_DOMAIN);
		return true;
	}

	// 该方法不外发
	public function convertUserInfo($arrUserInfo, $toCookie = true) {
		$arrMap = array(
			"uid"=>"uniqueid",
			"user"=>"userid",
			"ag"=>"appgroup",
			"email"=>"sinamail",
			"nick"=>"displayname",
			"name"=>"name",
			"sex"=>"gender",
			"dob"=>"birthday",
			"ps"=>"paysign"
			);
		$arrResult = array();
		foreach($arrMap as $key=>$val) {
			if ($toCookie) {
				$arrResult[$key] = $arrUserInfo[$val];
			} else {
				$arrResult[$val] = $arrUserInfo[$key];
			}
		}
		return array_merge($arrUserInfo,$arrResult);
	}
	public function getError() {
		return $this->_error;
	}

	public function getErrno() {
		return $this->_errno;
	}

	private function _setError($error,$errno=0) {
		$this->_error = $error;
		$this->_errno = $errno;
		return true;
	}
	private function _getCookieV1(&$arrUserInfo) {
		if( !isset($_COOKIE[self::COOKIE_SUE]) ||
			!isset($_COOKIE[self::COOKIE_SUP])) {
				$this->_setError("not all cookie are exists ");
				return false;
			}
		parse_str($_COOKIE[self::COOKIE_SUE],$arrSUE);
		parse_str($_COOKIE[self::COOKIE_SUP],$arrSUP);
		foreach( $arrSUP as $key=>$val) {
			$arrUserInfo[$key] = iconv("UTF-8","GBK",$val);
		}
		// 判断是否超时
		if($arrUserInfo["et"] < $this->_time) {
			$this->_setError("cookie timeout {et:".$arrUserInfo["et"].";now:".$this->_time."}");
			return false;
		}

		// 检查加密cookie
		$str = $this->_rawurlencode($_COOKIE[self::COOKIE_SUP]).$this->_arrConf[$arrSUE["ev"]];
		if($arrSUE["es2"] != md5($str)) {
			$this->_setError("encrypt string error");
			return false;
		}
		$arrUserInfo = $this->convertUserInfo($arrUserInfo, false);
		return true;
	}
	
	/**
	 * 微博自动登录
	 * 
	 * @return mixed
	 */
	public function autoLogin($entry) {
		$query = array(
			'entry'	=> $entry,
			'alc'	=> $_COOKIE[self::COOKIE_ALC],
			'ip'	=> $this->_getIp(),
			'domain'=> self::COOKIE_DOMAIN
		);
		
		$url = self::CHKALC_URL . '?' . http_build_query($query);
		$ret = file_get_contents($url);
		
		$result = array();
		parse_str($ret, $result);

		if ($result['result'] == 'succ') {
			//TODO 推荐JS做下面的处理。
			//当没有设置SSOLoginState时，代表用户退出浏览器又重新登录。此时需要自动登录一次，保证相关的cookie可以种到sina.com.cn中
//			if (!isset( $_COOKIE['SSOLoginState'])) {
//				self::setSinaLoginCookie($result['uniqueid']);
//				exit;
//			}

			//中SUE/SUP
			$cookies = explode("\n", $result['cookies']);
			foreach ($cookies as $cookie) {
				@header($cookie, false);
			}
			
			//获取SUE/SUP值，存入$_COOKIE，以防止同一次请求中多次验证用户登录状态
			$sue = $sup = array();
			preg_match('|SUE=(.*);|U', $result['cookies'], $sue);
			preg_match('|SUP=(.*);|U', $result['cookies'], $sup);
			$_COOKIE[SSOCookie::COOKIE_SUE] = rawurldecode($sue[1]);
			$_COOKIE[SSOCookie::COOKIE_SUP] = rawurldecode($sup[1]);
			
			unset($result['result'], $result['cookies']);
			return $this->convertUserInfo($result);
		}
		setcookie(self::COOKIE_ALC, '', 0, '/', self::COOKIE_DOMAIN,0,true);
		return false;
	}
	
	/**
	 * 设置cookie中的WEIBOALC
	 * 
	 * @param string $uid
	 * @param string $entry
	 * @param string $pin
	 * @return bool 
	 */
	public function setALCCookie($uid, $entry, $pin) {
		$ip = $this->_getIp();
		$query = array(
			'entry'	=> $entry,
			'user'	=> $uid,
			'ip'	=> $ip,
			'm'		=> md5($uid . $ip . $pin)
		);
		
		$url	= self::GETALC_URL . '?' . http_build_query($query);
		$ret	= file_get_contents($url);
		$result = array();
        parse_str($ret, $result);
        if ($result['result'] === 'succ') {
			$_COOKIE[self::COOKIE_ALC] = $result['alc'];
            setcookie(self::COOKIE_ALC, $result['alc'], $this->_time + 86400*7, '/', self::COOKIE_DOMAIN,0,true);
            return true;
        }
        return false;
	}

	/**
	 * parse cookie config file.
	 * @param $config: cookie config file
	 */
	private function _parseConfigFile($config) {
		$arrConf = @parse_ini_file($config);
		if(!$arrConf) {
			$this->_setError("parse file ".$config . " error");
			return false;
		}
		$this->_arrConf = $arrConf;
		return true;
	}
	private function _raw_http_build_query($arrQuery) {
		$arrtmp = array();
		foreach ($arrQuery as $key=>$val) {
			$arrtmp[] = $this->_rawurlencode($key)."=".$this->_rawurlencode($val);
		}
		return implode("&", $arrtmp);
	}
	private function _rawurlencode($str) {
		return str_replace('~','%7E',rawurlencode($str));
	}
	
	
	private function _getIp() {
		$xForward	= getenv('HTTP_X_FORWARDED_FOR');
		if ($xForward) {
			$arr = explode(',',$xForward);
			$cnt = count($arr);
			$xForward = $cnt==0 ? '' : trim($arr[$cnt-1]);
		}
		
		$remoteAddr	= getenv('REMOTE_ADDR');
		if ($this->_isPrivateIp($remoteAddr) && $xForward) {
			return $xForward;
		}
		return $remoteAddr;
	}
	private function _isPrivateIp($ip) {
		$i = explode('.', $ip);
		if ($i[0] == 10 || ($i[0] == 172 && $i[1] > 15 && $i[1] < 32) || ($i[0] == 192 && $i[1] == 168)) {
			return true;
		}
		return false;
	}
}