<?php
/**
 * domain对照表
 */
class Sso_Sdk_Tools_Domain {
	/**
	 * 将名字转化为相应的编码
	 * @param $name string
	 * @return null|int
	 */
	public static function name2code($name) {
		$arr = Sso_Sdk_Config::instance()->get("data.domain");
		return isset($arr[$name])?$arr[$name]:null;
	}

	/**
	 * 将编码转化为相应的名字
	 * @param $code int
	 * @return null|string
	 */
	public static function code2name($code) {
		$arr = array_flip(Sso_Sdk_Config::instance()->get("data.domain"));
		return isset($arr[$code])?$arr[$code]:null;
	}
}