<?php
class Tool_Version {
	
	const VERSION_V36 = 'v36';
	const VERSION_V4 = 'v4';
	const VERSION_36 = '3.6';
	
	public static function get_js_version($js_version){
		$version = Comm_Weibo_JsVersion::get_js_version();
		
		if ($version){
			return $version;
		}else{
			return $js_version;
		}
	}
	
	public static function get_version() {
		$version = Comm_Context::cookie('wvr', NULL);
		return $version;
	}
	
	public static function get_version_mark() 
	{
		$version = self::get_version();
		try {
			switch($version) {
				case self::VERSION_36:
					return self::VERSION_V36;
					break;
				default:
					return self::VERSION_V4;
					break;
			}
	
		} catch (Comm_Exception_Program $e) {
			return self::VERSION_V4;
		}
	}

	public static function get_mobile_version(){
        $params = array_merge($_GET, $_POST);
        $from = $params['from'];
        //客户端类型, 501--安卓, 301--iphone
        $type = substr($from, -4, 3);
        //客户端版本
        $version = substr($from, 2,3);
		return $version;
	}
}
