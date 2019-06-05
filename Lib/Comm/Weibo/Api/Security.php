<?php
/**
 * 调用api.sc.weibo.com 接口，管理商户系统密码相关信息 
 *
 * @package
 * @copyright  copyright(2014) weibo.com all rights reserved
 * @author     chengmeng@staff.sina.com.cn
 * @version    2014-07-18
 */
class Comm_Weibo_Api_Security {
	const MERSYS_SECURITY = 'http://api.sc.weibo.com/v2/merchant/security/' ;
	
	public static function add_pwd() {
		$url = self::MERSYS_SECURITY . 'add' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
		
		$platform->add_rule('sign', 'string', true) ;
		$platform->add_rule('merchant_id', 'string', true) ;
		$platform->add_rule('password', 'string', true) ;
		$platform->add_rule('question', 'string', true) ;
		$platform->add_rule('answer', 'string', true) ;
		
		return $platform ;
	}
	
	public static function modify_pwd() {
		$url = self::MERSYS_SECURITY . 'modify' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('sign', 'string', true) ;
		$platform->add_rule('merchant_id', 'string', true) ;
		$platform->add_rule('password', 'string', true) ;
		$platform->add_rule('new_password', 'string', true) ;
	
		return $platform ;
	}
	
	public static function verify_pwd() {
		$url = self::MERSYS_SECURITY . 'verify' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('sign', 'string', true) ;
		$platform->add_rule('merchant_id', 'string', true) ;
		$platform->add_rule('password', 'string', true) ;
	
		return $platform ;
	}
	
	public static function query_security_ques() {
		$url = self::MERSYS_SECURITY . 'question' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('sign', 'string', true) ;
		$platform->add_rule('merchant_id', 'string', true) ;
	
		return $platform ;
	}
	
	public static function apply_find_pwd() {
		$url = self::MERSYS_SECURITY . 'find/apply' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('sign', 'string', true) ;
		$platform->add_rule('merchant_id', 'string', true) ;
		$platform->add_rule('question', 'string', true) ;
		$platform->add_rule('answer', 'string', true) ;
	
		return $platform ;
	}
	
	public static function reset_pwd() {
		$url = self::MERSYS_SECURITY . 'find/reset' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('sign', 'string', true) ;
		$platform->add_rule('merchant_id', 'string', true) ;
		$platform->add_rule('password', 'string', true) ;
	
		return $platform ;
	}
}