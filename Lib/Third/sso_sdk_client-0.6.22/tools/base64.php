<?php
/**
 * Base64编码类
 */

class Sso_Sdk_Tools_Base64 {

	//默认base64字符集
	const CHARSET_DEFAULT   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	//url安全的base64字符集
	const CHARSET_URL_SAFE  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.';


	/**
	 * 默认的base64编码
	 * @param $str
	 * @return string
	 */
	public static function encode($str){
		return self::encode_by_charset($str, self::CHARSET_DEFAULT);
	}

	/**
	 * 默认的base64解码
	 * @param $str
	 * @return string
	 */
	public static function decode($str){
		return self::decode_by_charset($str, self::CHARSET_DEFAULT);
	}

	/**
	 * url安全的base64编码
	 * @param $str
	 * @return string
	 */
	public static function urlsafe_encode($str) {
		return self::encode_by_charset($str, self::CHARSET_URL_SAFE);
	}

	/**
	 * url安全的base64解码，没有使用本类中的方法，是为了提高效率
	 * @param $str
	 * @return string
	 */
	public static function urlsafe_decode($str) {
		return base64_decode(str_replace(array('-','_','.'), array('+', '/', '='), $str));
	}

	/**
	 * @param $str
	 * @param $charset
	 * @throws Exception
	 * @return string
	 */
	public static function encode_by_charset($str, $charset) {
		$base64EncodeChars = $charset;
		$len = strlen($str);
		$i = 0;
		$out = "";
		$pad = $base64EncodeChars[64];

		while($i < $len) {
			$c1 = ord($str[$i++]) & 0xff;
			if($i == $len) {
				$out .= $base64EncodeChars[($c1 >> 2)];
				$out .= $base64EncodeChars[($c1 & 0x3) << 4];
				$out .= $pad.$pad ;
				break;
			}
			$c2 = ord($str[$i++]);
			if($i == $len) {
				$out .= $base64EncodeChars[$c1 >> 2];
				$out .= $base64EncodeChars[(($c1 & 0x3)<< 4) | (($c2 & 0xF0) >> 4)];
				$out .= $base64EncodeChars[($c2 & 0xF) << 2];
				$out .= $pad;
				break;
			}
			$c3 = ord($str[$i++]);
			$out .= $base64EncodeChars[$c1 >> 2];
			$out .= $base64EncodeChars[(($c1 & 0x3)<< 4) | (($c2 & 0xF0) >> 4)];
			$out .= $base64EncodeChars[(($c2 & 0xF) << 2) | (($c3 & 0xC0) >>6)];
			$out .= $base64EncodeChars[$c3 & 0x3F];
		}
		return $out;
	}

	/**
	 * @param $str
	 * @param $charset
	 * @return string
	 */
	public static function decode_by_charset($str, $charset) {
		$base64EncodeChars = $charset;
		$base64DecodeChars = '';
		for ($i = 0; $i < 64; $i++) {
			$base64DecodeChars[ord($charset[$i])] = $i;
		}

		$len = strlen($str);
		$i = 0;
		$out = "";
		$pad = $base64EncodeChars[64];
		while($i < $len) {
			/* $c1 */
			do {	//此while循环有踢出非合法字符的功能
				$c1 = $base64DecodeChars[ord($str[$i++]) & 0xff];
			} while($i < $len && $c1 === null);
			if($c1 === null) break;

			/* $c2 */
			do {
				$c2 = $base64DecodeChars[ord($str[$i++]) & 0xff];
			} while($i < $len && $c2 === null);
			if($c2 === null) break;

			$out .= chr(($c1 << 2) | (($c2 & 0x30) >> 4));

			/* $c3 */
			do {
				if($str[$i] == $pad) return $out;
				$c3 = $base64DecodeChars[ord($str[$i++]) & 0xff];
			} while($i < $len && $c3 === null);
			if($c3 === null) break;

			$out .= chr((($c2 & 0XF) << 4) | (($c3 & 0x3C) >> 2));

			/* $c4 */
			do {
				if($str[$i] == $pad) return $out;
				$c4 = $base64DecodeChars[ord($str[$i++]) & 0xff];
			} while($i < $len && $c4 === null);
			if($c4 === null) break;
			$out .= chr((($c3 & 0x03) << 6) | $c4);
		}
		return $out;
	}

	public static function sso_urlsafe_encode($str) {
	    return str_replace(array('+', '/', '='), array('-','_','.'), base64_encode($str));
	}
}
