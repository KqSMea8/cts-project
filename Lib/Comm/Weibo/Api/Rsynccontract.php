<?php
/**
 * 调用管理后台合同数据同步接口
 * @author  	chengmeng@staff.sina.com.cn
 * @version 	2014-03-04
 * @copyright  	copyright(2014) weibo.com all rights reserved
 */
class Comm_Weibo_Api_Rsynccontract 
{
	public static function create_contract($url)
	{
		Comm_Weibo_Api_Request_ThirdModule::set_check_succ_ret(false);
		
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, "POST");
		
		$platform->add_rule("contract_id", "string");
		$platform->add_rule("contract_sub_id", "string");
		$platform->add_rule("firm_name", "string");
		$platform->add_rule("firm_id", "string");
		$platform->add_rule("stime", "string");
		$platform->add_rule("etime", "string");
		$platform->add_rule("yewuflag", "string");
		$platform->add_rule("account_number", "string");
		$platform->add_rule("contract_type", "string");
		$platform->add_rule("opration", "string");
		$platform->add_rule("share_type", "string");
		$platform->add_rule("share_amount", "string");
		$platform->add_rule("status", "int64");
		$platform->add_rule("create_time", "string");
		$platform->add_rule("update_time", "string");
		$platform->add_rule("sign", "string");
		$platform->add_rule("ts", "int64") ;
		$platform->add_rule("platform", "string") ;
		
		return $platform;
	}
}