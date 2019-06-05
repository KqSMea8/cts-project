<?php
/**
 * 调用buy.sc.weibo.com的支付圈签约查询接口
 *
 * @package    
 * @copyright  copyright(2014) weibo.com all rights reserved
 * @author     zhongwei4 <zhongwei4@staff.sina.com.cn>
 * @version    2014-02-14
 */

class Comm_Weibo_Api_Protocolquery {
	public static function get_html($url){
        Comm_Weibo_Api_Request_ThirdModule::set_check_succ_ret(false);
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, "GET");
		$platform->add_rule("platform", "int64");
		$platform->add_rule("aliuser", "string");
        $platform->add_rule("ts", "string");
        $platform->add_rule("sign", "string");
		return $platform;
	}
}