<?php
class Comm_Weibo_Remit {
	const REMIT_URL = 'http://api.sc.weibo.com/v2/remit/apply' ;
	const ALIPAY_QUERY = 'http://api.sc.weibo.com/v2/trade/aidquery' ;
	const REFUND_URL = 'http://api.sc.weibo.com/v2/pay/refund/refund' ;
	const CREATE_COUPON = 'http://i.pay.vip.weibo.com/coupon/create' ;
	
	public static function remit_apply() {
		try {
			$platform = new Comm_Weibo_Api_Request_ThirdModule(self::REMIT_URL, 'POST') ;
			$platform->set_check_succ_ret(false) ;
			
			$platform->add_rule('sign', 'string', true) ;
			$platform->add_rule('batch_no', 'string', true) ;
			$platform->add_rule('uid', 'int64', false) ;
			$platform->add_rule('seller_id', 'int64', true) ;
			$platform->add_rule('trade_id', 'string', false) ;
			$platform->add_rule('alipay_account', 'string', false) ;
			$platform->add_rule('alipay_name', 'string', false) ;
			$platform->add_rule('alipay_uid', 'string', false) ;
			$platform->add_rule('amount', 'float', true) ;
			$platform->add_rule('reason', 'string', true) ;
			$platform->add_rule('notify_url', 'string', false) ;
			$platform->add_rule('remit_time', 'string', false) ;
			$platform->add_rule('extra', 'string', false) ;
			
			return $platform ;
		} catch (Exception $e){
			Tool_Log::warning($e->getMessage()) ;
			return ;
		}
	}
	
	public static function query_alipay() {
		try {
			$platform = new Comm_Weibo_Api_Request_ThirdModule(self::ALIPAY_QUERY, 'POST') ;
			$platform->set_check_succ_ret(false) ;
				
			$platform->add_rule('sign', 'string', true) ;
			$platform->add_rule('trade_id', 'string', true) ;
				
			return $platform ;
		} catch (Exception $e){
			Tool_Log::warning($e->getMessage()) ;
			return ;
		}
	}
	
	public static function apply_refund() {
		try {
			$platform = new Comm_Weibo_Api_Request_ThirdModule(self::REFUND_URL, 'POST') ;
			$platform->set_check_succ_ret(false) ;
				
			$platform->add_rule('source', 'string', true) ;
			$platform->add_rule('uid', 'string', true) ;
			$platform->add_rule('sign_type', 'string', true) ;
			$platform->add_rule('sign', 'string', true) ;
			$platform->add_rule('refund_url', 'string', true) ;
			$platform->add_rule('batch_no', 'string', true) ;
			$platform->add_rule('detail_data', 'string', true) ;
			$platform->add_rule('batch_num', 'int', true) ;;
				
			return $platform ;
		} catch (Exception $e){
			Tool_Log::warning($e->getMessage()) ;
			return ;
		}
	}
	
	public static function create_coupon() {
		try {
			$platform = new Comm_Weibo_Api_Request_ThirdModule(self::CREATE_COUPON, 'POST') ;
			$platform->set_check_succ_ret(false) ;
	
			$platform->add_rule('uid', 'string', true) ;
			$platform->add_rule('days', 'int', FALSE) ;
			$platform->add_rule('money', 'int', FALSE) ;
			$platform->add_rule('code', 'int', FALSE) ;
	
			return $platform ;
		} catch (Exception $e){
			Tool_Log::warning($e->getMessage()) ;
			return ;
		}
	}
}