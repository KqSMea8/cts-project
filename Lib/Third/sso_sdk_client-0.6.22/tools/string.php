<?php

/**
 * 字符串处理相关类
 * Class Sso_Sdk_Tools_String
 */
class Sso_Sdk_Tools_String {
	const DIGIT = '0123456789',
		ALPHA   = 'ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz',
		ALNUM   = 'ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz0123456789',
		UPNUM   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
		LONUM   = 'abcdefghijklmnopqrstuvwxyz0123456789',
		LOWER   = 'abcdefghijklmnopqrstuvwxyz',
		UPPER   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
		HUMAN   = '2345678ABCEFGHKMNPQRSUVWXYZabcdefhkmnpqsuvwxyz';

	/**
	 * 生成指定长度的随机字符串
	 * @param $chars string
	 * @param $length int
	 * @return string
	 */
	public static function rand($chars, $length) {
		$size   = strlen($chars)-1;
		$str    = '';
		for ($i=0; $i<$length; $i++) {
			$str .= $chars[mt_rand(0, $size)];
		}
		return $str;
	}

	/**
	 * 按照指定规则pack一段信息
	 * @param $val mixed
	 * @param $type string
	 * @return null|string
	 */
	public static function pack($val, $type){
		switch($type) {
			case 'c1':
				return  chr($val);
			case 'c2':
				return  pack('n',$val);
			case 'c4':
				return  pack('N',$val);
			case 'timestamp':
				return  pack('N',$val);
			case 'ip':
				return  pack('N',ip2long($val));
			default:
				return null;
		}
	}

	/**
	 * 按照指定类型unpack一段数据
	 * @param $val
	 * @param $type
	 * @return int|null|string
	 */
	public static function unpack($val, $type){
		switch($type) {
			case 'c1':
				return  ord($val);
			case 'c2':
				$arr = unpack('n',$val);
				return (int)array_shift($arr); //array_shift接受的是一个引用参数，不能直接使用unpack的返回值
			case 'c4':
				$arr = unpack('N',$val);
				return  (int)array_shift($arr);
			case 'c*':
				$arr = unpack('H*', $val);
				return  array_shift($arr);
			case 'timestamp':
				$arr = unpack('N',$val);
				return  (int)array_shift($arr);
			case 'ip':
				$arr = unpack('N',$val);
				return  (string)long2ip(array_shift($arr));
			default:
				return null;
		}
	}
}