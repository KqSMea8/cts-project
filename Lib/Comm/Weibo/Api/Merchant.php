<?php
/**
 * 调用api.sc.weibo.com获取商户系统所需信息
 *
 * @package
 * @copyright  copyright(2014) weibo.com all rights reserved
 * @author     chengmeng@staff.sina.com.cn
 * @version    2014-05-07
 */

class Comm_Weibo_Api_Merchant {
	const REQUEST_URL = 'http://api.sc.weibo.com/v2/merchant/';
	const MERSYS_V2_URL = 'http://api.sc.weibo.com/v2/';
    const MERCHANT_URL_V2 = 'http://pay.sc.weibo.com/api/operate/merchant/';
	
	public static function get_merchant_info()
	{
		$url = self::REQUEST_URL . 'show';
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
		
		$platform->add_rule('merchant_id', "int64",TRUE) ;
		$platform->add_rule('sign', "string", TRUE) ;
		
		return $platform ;
	}
	
	public static function get_trade_info($suffix)
	{
		$url = self::REQUEST_URL . $suffix ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST') ;
		$platform->set_check_succ_ret(false);
		
		$platform->add_rule('merchant_id', 'int64', TRUE) ;
		$platform->add_rule('sign', 'string', TRUE) ;
		$platform->add_rule('subject', 'string', FALSE) ;
		$platform->add_rule('buyer_id', 'int64', FALSE) ;
		$platform->add_rule('s_status', 'int', FALSE) ;
		$platform->add_rule('o_status', 'int', FALSE) ;
		$platform->add_rule('trade_id', 'string', FALSE) ;
		$platform->add_rule('out_trade_no', 'string', FALSE) ;
		$platform->add_rule('start_time', 'string', FALSE) ;
		$platform->add_rule('end_time', 'string', FALSE) ;
		$platform->add_rule('page_size', 'int', FALSE) ;
		$platform->add_rule('page_no', 'int', FALSE) ;
		
		return $platform;
	}
	
	public static function get_trade_account($suffix) {
		
		$url = self::REQUEST_URL . $suffix;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
		
		$platform->add_rule('merchant_id', "int64",TRUE) ;
		$platform->add_rule('sign', "string", TRUE) ;
		$platform->add_rule('page_size', 'int', FALSE) ;
		$platform->add_rule('page_no', 'int', FALSE) ;
		
		return $platform ;
	}
	
	public static function check_merchant() {
		$url = self::REQUEST_URL . 'exist' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
		
		$platform->add_rule('merchant_id', "int64",TRUE) ;
		$platform->add_rule('sign', "string", TRUE) ;
		
		return $platform ;
	}

    public static function get_merchant_info_pay2() {
        $url = self::MERCHANT_URL_V2 . 'detail' ;
        $platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
        $platform->set_check_succ_ret(false);

        $platform->add_rule('uid', "int64",TRUE) ;
        $platform->add_rule('sign', "string", TRUE) ;
        $platform->add_rule('sign_type', "string", TRUE) ;

        return $platform ;
    }
	
	public static function remedy_batch() {
		
		$url = self::REQUEST_URL . 'trade/remedy';
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
		
		$platform->add_rule('merchant_id', "int64",TRUE) ;
		$platform->add_rule('sign', "string", TRUE) ;
		$platform->add_rule('task_ids', 'string', TRUE) ;
		
		return $platform ;
	}
	
    public static function check_merchant_exist(){
		$url = self::REQUEST_URL . 'exist';
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
		$platform->set_check_succ_ret(false);
		
		$platform->add_rule('merchant_id', "int64",TRUE) ;
		$platform->add_rule('sign', "string", TRUE) ;
		
		return $platform ;
    }
    
    public static function modify_merchant_info() {
    
    	$url = self::REQUEST_URL . 'update';
    	$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
    	$platform->set_check_succ_ret(false);
    
    	$platform->add_rule('merchant_id', "int64",TRUE) ;
    	$platform->add_rule('weibo_account', "string",TRUE) ;
    	$platform->add_rule('sign', "string", TRUE) ;
    	$platform->add_rule('overview', 'string', FALSE) ;
    	$platform->add_rule('hotline', 'string', FALSE) ;
    	$platform->add_rule('boss_name', 'string', FALSE) ;
    	$platform->add_rule('boss_phone', 'string', FALSE) ;
    	return $platform ;
    }
    
    public static function trade_overview() {
    	$url = self::MERSYS_V2_URL . 'trade/overview' ;
    	 
    	$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
    	$platform->set_check_succ_ret(false);
    	 
    	$platform->add_rule('seller_id', "int64",TRUE) ;
    	$platform->add_rule('sign', "string", TRUE) ;
    	return $platform ;
    }
    
    public static function trade_list() {
    	$url = self::MERSYS_V2_URL . 'trade/list' ;
    	 
    	$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
    	$platform->set_check_succ_ret(false);
    
    	$platform->add_rule('seller_id', "string",TRUE) ;
    	$platform->add_rule('trade_id', "string",FALSE) ;
    	$platform->add_rule('out_trade_no', "string",FALSE) ;
    	$platform->add_rule('buyer_id', "string", FALSE) ;
    	$platform->add_rule('start_time', 'string', FALSE) ;
    	$platform->add_rule('end_time', 'string', FALSE) ;
    	$platform->add_rule('page_size', 'int', FALSE) ;
    	$platform->add_rule('page_no', 'int', FALSE) ;
    	$platform->add_rule('sign', "string", TRUE) ;
    	return $platform ;
    }
    
   	public static function distri() {
   		$url = self::MERSYS_V2_URL . 'distri/apply' ;
   		
   		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
   		$platform->set_check_succ_ret(false);
   		
   		$platform->add_rule('seller_id', "string",TRUE) ;
   		$platform->add_rule('trade_id', "string",TRUE) ;
   		$platform->add_rule('amount', "string",TRUE) ;
   		$platform->add_rule('sec_parameters', "string", FALSE) ;
   		$platform->add_rule('sign', "string", TRUE) ;
   		return $platform ;
   	}
   	
   	public static function distri_trace() {
   		$url = self::MERSYS_V2_URL . 'distri/trace' ;
   		 
   		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
   		$platform->set_check_succ_ret(false);
   		 
   		$platform->add_rule('seller_id', "string",TRUE) ;
   		$platform->add_rule('trace_type', "string",TRUE) ;
   		$platform->add_rule('dis_ids', "string",FALSE) ;
   		$platform->add_rule('trade_ids', "string", FALSE) ;
   		$platform->add_rule('sign', "string", TRUE) ;
   		return $platform ;
   	}
   	
   	public static function refund() {
   		$url = self::MERSYS_V2_URL . 'refund/apply' ;
   		
   		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
   		$platform->set_check_succ_ret(false);
   		
   		$platform->add_rule('seller_id', "string",TRUE) ;
   		$platform->add_rule('trade_id', "string",TRUE) ;
   		$platform->add_rule('refund_no', "string",TRUE) ;
   		$platform->add_rule('amount', "string", TRUE) ;
   		$platform->add_rule('reason', 'string', FALSE) ;
   		$platform->add_rule('sec_parameters', 'string', FALSE) ;
   		$platform->add_rule('notify_url', 'int', FALSE) ;
   		$platform->add_rule('sign', "string", TRUE) ;
   		return $platform ;
   	}
   	
   	public static function refund_trace() {
   		$url = self::MERSYS_V2_URL . 'refund/trace' ;
   		 
   		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST');
   		$platform->set_check_succ_ret(false);
   		 
   		$platform->add_rule('seller_id', "string", TRUE) ;
   		$platform->add_rule('trace_type', "string", TRUE) ;
   		$platform->add_rule('re_ids', "string", FALSE) ;
   		$platform->add_rule('trade_ids', "string", FALSE) ;
   		$platform->add_rule('sign', "string", TRUE) ;
   		return $platform ;
   	}
}