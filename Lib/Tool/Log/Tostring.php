<?php
class Tool_Log_Tostring {
	public static function arr2str($arr) {
		try {
			if (is_null($arr)) {
				return "null";
			}
			if (is_string($arr) || is_numeric($arr)) {
				return $arr;
			}
			if ($arr === true) {
				return 'true';
			}
			if ($arr === false) {
				return 'false';
			}
			if (is_object($arr)) {
				return "object";
			}
			if (!is_array($arr)) {
				return "unrecognized";
			}
			$ret = "[";
			foreach($arr as $k=>$v) {
				$ret .= $k ."=>". self::arr2str($v).",";
			}
			$ret = rtrim($ret, ",");
			$ret .= "]";
			return $ret;
		} catch (Exception $e) {
			return "Exception";
		}
	}
}
