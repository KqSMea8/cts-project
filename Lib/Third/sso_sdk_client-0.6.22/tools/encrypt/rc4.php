<?php
/**
 * RC4加解密算法
 */

class Sso_Sdk_Tools_Encrypt_RC4 {

	/**
	 * 加密
	 * @param $data string
	 * @param $key string
	 * @return string
	 */
	public static function encrypt($data, $key) {
		$s = array();
		for ($i=0; $i<256; $i++) {
			$s[$i] = $i;
		}

		$j = 0;
		$key_len = strlen($key);
		for ($i=0; $i<256; $i++) {
			$j = ($j + $s[$i] + ord($key[$i % $key_len])) % 256;
			//swap
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
		}
		$i = 0;
		$j = 0;
		$ct = '';
		$data_len = strlen($data);
		for ($y=0; $y< $data_len; $y++) {
			$i = ($i + 1) % 256;
			$j = ($j + $s[$i]) % 256;
			//swap
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
			$ct .= $data[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
		}
		return $ct;
	}

	/**
	 * 解密
	 * @param $data string
	 * @param $key string
	 * @return string
	 */
	public static function decrypt($data, $key) {
		return self::encrypt($data, $key);
	}
}