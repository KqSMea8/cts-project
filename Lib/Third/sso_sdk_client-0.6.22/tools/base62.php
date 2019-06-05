<?php
class Sso_Sdk_Tools_Base62 {

	private static $string = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

	public static function encode($str) {
		if ($str == 0) return 0;
		$out = '';
		for($t=floor(log10($str)/log10(62)); $t>=0; $t--) {
			$a = floor($str / pow(62, $t));
			$out = $out.substr(self::$string, $a, 1);
			$str = $str - ($a * pow(62, $t));
		}
		return $out;
	}

	public static function decode($str) {
		$out = 0;
		$len = strlen($str) - 1;
		for($t=0; $t<=$len; $t++) {
			$out = $out + strpos(self::$string, substr($str, $t, 1)) * pow(62, $len - $t);
		}
		return substr(sprintf("%f", $out), 0, -7);
	}
}