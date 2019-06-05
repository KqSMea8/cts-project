<?php

class Comm_Weibo_Api_Ship {
	public static function get_html($url){
        Comm_Weibo_Api_Request_ThirdModule::set_check_succ_ret(false);
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, "POST");
		$platform->add_rule("platform", "int64");
        $platform->add_rule("firmid", "int64");
		$platform->add_rule("orderid", "int64");
        $platform->add_rule("logistic", "string");
        $platform->add_rule("waybill", "string");
        $platform->add_rule("sign", "string");
		return $platform;
	}
}