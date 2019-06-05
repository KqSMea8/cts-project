<?php

class Comm_Weibo_Api_Cpcode {
	public static function get_html($url){
        Comm_Weibo_Api_Request_ThirdModule::set_check_succ_ret(false);
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, "GET");
		$platform->add_rule("dimension", "string");
        $platform->add_rule("appName", "string");
		$platform->add_rule("digest", "string");
        $platform->add_rule("mailNo", "string");
        $platform->add_rule("cpCode", "string");
		return $platform;
	}
}