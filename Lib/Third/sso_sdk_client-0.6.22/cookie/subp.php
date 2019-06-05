<?php
/*
 * SUBP结构说明文档: http://wiki.intra.sina.com.cn/x/8pKl
 * cookie中各个字段的value以utf-8编码
 */

/**
 * Class Sso_Sdk_Cookie_SUBP
 */
class Sso_Sdk_Cookie_SUBP {
	const COOKIE_NAME = "SUBP";

	private static $_arr_subp_field_map = array(
		'status'        => '70a', // SUBP 状态，同 SUB 状态。
		'flag'          => '2bf', // SUBP 标志位，同 SUB 标识位 flag 。
		'evid'          => 'e0f', // 加密的访客 id 。
		'ln'            => 'e0e', // 登录名 。
		'spr'           => 'e0a', // 就是spr 。
		'refer'         => 'a1e', // 精确到域名。
		'appid'         => '2b8', // 就是appid 。
	);

	/**
	 * 解析SUBP
	 * @param $subp
	 * @return array|null
	 */
	public static function parse($subp) {
		$version = preg_replace("/^0+/", "", substr($subp, 0, 3));
		switch($version) {
			case 1:
				return self::_parse_v1($subp);
			case 2:
				return self::_parse_v2($subp);
			case 3:
				return self::_parse_v3($subp);
			default:
				return null;
		}
	}
	/**
	 * v3版本的解析方法
	 * @param $subp
	 * @return array
	 * @throws Exception
	 */
	private static function _parse_v3($subp) {
		$key_version = substr($subp, 3, 1);
		$subp_key = Sso_Sdk_Config::instance()->get('data.key.subp');
		if (!isset($subp_key["v$key_version"])) {
			throw new Exception('subp key not config');
		}
		$info = array();
		$data = Sso_Sdk_Tools_Base64::decode_by_charset(substr($subp, 4), $subp_key["v$key_version"]);
		$offset = 0;
		while($offset < strlen($data)) {
			$key_len = ord(substr($data, $offset, 1)); $offset += 1;
			$key = substr($data, $offset, $key_len);  $offset += $key_len;
			$value_len = ord(substr($data, $offset, 1)); $offset += 1;
			$value = substr($data, $offset, $value_len); $offset += $value_len;
			if (!$key) break;

			$info[$key] = $value;
		}
		return (array)self::_decode_data($info);
	}
	/**
	 * v2版本的解析方法
	 * @param $subp
	 * @return array
	 * @throws Exception
	 */
	private static function _parse_v2($subp) {
		$subp_key = Sso_Sdk_Config::instance()->get('data.key.subp');
		if (!isset($subp_key["v2"])) {
			throw new Exception('subp key not config');
		}
		$info = array();
		$data = Sso_Sdk_Tools_Base64::decode_by_charset(substr($subp, 3), $subp_key["v2"]);
		$offset = 0;
		while($offset < strlen($data)) {
			$key_len = ord(substr($data, $offset, 1)); $offset += 1;
			$key = substr($data, $offset, $key_len);  $offset += $key_len;
			$value_len = ord(substr($data, $offset, 1)); $offset += 1;
			$value = substr($data, $offset, $value_len); $offset += $value_len;
			if (!$key) break;

			$info[$key] = $value;
		}
		return (array)self::_decode_data($info);
	}

	/**
	 * v1版本的解析方法
	 * @param $subp
	 * @return bool
	 */
	private static function _parse_v1($subp) {
		$_arr_subp_field_map = array_flip(self::$_arr_subp_field_map);
		$offset = 3;
		$info = array();
		while($offset < strlen($subp) - 3) {
			$fkey = substr($subp, $offset, 3);  $offset += 3;
			$len_flag = substr($subp, $offset, 1); $offset += 1;
			$len_value = hexdec(substr($subp, $offset, hexdec($len_flag))); $offset += hexdec($len_flag);
			$value = pack("H*", substr($subp, $offset, $len_value)); $offset += $len_value;
			$info[$_arr_subp_field_map[$fkey]] = $value;
		}
		return (array)self::_decode_data($info);
	}

	private static function _decode_data($arr) {
		foreach($arr as $k=>$v) {
			switch($k) {
				case 'status':
					$arr[$k] = Sso_Sdk_Tools_String::unpack($v, 'c1');break;
				case 'flag':
					$arr[$k] = Sso_Sdk_Tools_String::unpack($v, 'c2');break;
			}
		}
		return $arr;
	}

}