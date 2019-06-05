<?php
/**
 * idc对照表
 */
class Sso_Sdk_Tools_IDC {
	/**
	 * 将idc名字转化为相应的编码
	 * @param $name
	 * @return null
	 */
	public static function name2code($name) {
		$arr = Sso_Sdk_Config::instance()->get("data.idc");
		return isset($arr[$name])?$arr[$name]:null;
	}

	/**
	 * 将编码转化为相应的idc名字
	 * @param $code
	 * @return null
	 */
	public static function code2name($code) {
		$arr = array_flip(Sso_Sdk_Config::instance()->get("data.idc"));
		return isset($arr[$code])?$arr[$code]:null;
	}
}