<?php
class Sso_Sdk_Tools_Cookie {
	/**
	 * 删除cookie
	 * @param $name
	 * @param string $path
	 * @param string $domain
	 * @return bool
	 */
	public static function delete($name, $path='', $domain='') {
		@setcookie($name, 'deleted', 1, $path, $domain);
		return true;
	}

	/**
	 * 根据header字符串设置cookie
	 * @param $str
	 */
	public static function set_by_header_string($str) {
		header($str, false);
	}
	/**
	 * 使用header方式输出set cookie
	 * @param $name
	 * @param $value
	 * @param $expire
	 * @param string $path
	 * @param null $domain
	 * @param bool $secure
	 * @param bool $httponly
	 */
	public static function set_by_header($name, $value, $expire, $path='/', $domain=null, $secure=false, $httponly=false) {
		self::set_by_header_string(self::generate_header($name, $value, $expire, $path, $domain, $secure, $httponly));
	}

	/**
	 * 生成set cookie字符串
	 * @param $name
	 * @param $value
	 * @param $expire
	 * @param string $path
	 * @param null $domain
	 * @param bool $secure
	 * @param bool $httponly
	 * @param bool $header_name
	 * @return string
	 */
	public static function generate_header($name, $value, $expire, $path='/', $domain=null, $secure=false, $httponly=false, $header_name = true) {
		$cookie = $header_name?"Set-Cookie: ":"";
		$cookie .= "{$name}={$value}";
		$expire && $cookie .= "; expires=". gmdate(DATE_COOKIE, $expire);
		$path && $cookie .= "; path={$path}";
		$domain && $cookie .= "; domain={$domain}";
		$secure && $cookie .= '; secure';
		$httponly && $cookie .= '; httponly';
		return $cookie;
	}
}