<?php

class Comm_Weibo_Api_Qrcode {
	public static function get_html($url){
        Comm_Weibo_Api_Request_ThirdModule::set_check_succ_ret(false);
        Comm_Weibo_Api_Request_ThirdModule::set_check_json_decode(false);
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, "GET");
		$platform->add_rule("qr", "string", true);        //二维码数据集合
		return $platform;
	}

    /**
     * gen_qrcode 
     * weibo公共生成二维码方法
     * @param mixed $url 
     * @static
     * @return void
     */
	public static function gen_qrcode($url){
        Comm_Weibo_Api_Request_ThirdModule::set_check_succ_ret(false);
        Comm_Weibo_Api_Request_ThirdModule::set_check_json_decode(false);
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, "GET");
		$platform->add_rule("api_key", "string", true);
		$platform->add_rule("title", "string", true);
		$platform->add_rule("data", "string", true);
		$platform->add_rule("type", "string", true);
		$platform->add_rule("level", "string", false);
		$platform->add_rule("start_time", "string", false);
		$platform->add_rule("deadline", "string", false);
		$platform->add_rule("size", "string", false);
		$platform->add_rule("margin", "string", false);
		$platform->add_rule("background", "string", false);
		$platform->add_rule("foreground", "string", false);
		$platform->add_rule("logo", "string", false);
		$platform->add_rule("redirect", "string", false);
		$platform->add_rule("extparam", "string", false);
		$platform->add_rule("borderwidth", "string", false);
		$platform->add_rule("bordercolor", "string", false);
		$platform->add_rule("output_type", "string", false);
		$platform->add_rule("datetime", "string", false);
		$platform->add_rule("sign", "string", false);
		return $platform;
	}
}