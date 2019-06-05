<?php
class Comm_Weibo_Api_Installapps {
	const PRE_URL = 'http://i2.api.weibo.com/2/' ;
		
	public static function get_app_list() {
		$url = self::PRE_URL . 'tabs/tabs_list.json' ;
		$platform = new Comm_Weibo_Api_Request_Platform($url, 'GET');
		
		$platform->add_rule('uid', "string", false) ;
		$platform->add_rule('filter_by_type', "int", false) ;
		$platform->add_rule('filter_by_platform', "string", false) ;
		
		return $platform ;
	}
	
	
}