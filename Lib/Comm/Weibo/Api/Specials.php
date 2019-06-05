<?php
class Comm_Weibo_Api_Specials {
	const URL = 'http://api.sc.weibo.com/v2/collection' ;
	const STATUSES_URL = 'http://i2.api.weibo.com/2/statuses/show.json' ; 
	public static function get_products() {
		$url = self::URL . '/plan/list' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false);
		
		$platform->add_rule('title', 'string', false) ;
		$platform->add_rule('seller_id', 'int64', false) ;
		$platform->add_rule('seller_name', 'string', false) ;
		$platform->add_rule('from_id', 'string', false) ;
		$platform->add_rule('app_id', 'string', false) ;
		$platform->add_rule('category', 'string', false) ;
		$platform->add_rule('status', 'int', false) ;
		$platform->add_rule('time_type', 'string', false) ;
		$platform->add_rule('start', 'string', false) ;
		$platform->add_rule('end', 'string', false) ;
		$platform->add_rule('limit', 'int', false) ;
		$platform->add_rule('offset', 'int', false) ;
		$platform->add_rule('order', 'string', false) ;
		$platform->add_rule('asc', 'int', false) ;
		$platform->add_rule('sign', 'string', true) ;
		return $platform ;
	}
	
	public static function get_category() {
		$url = self::URL . '/category/get' ;
		
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false) ;
		
		return $platform ;
	}
	
	public static function get_app_name() {
		$url = self::URL . '/app/get' ;
		
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false) ;
		
		return $platform ;
	}
	
	public static function get_messages($id) {
		$platform = new Comm_Weibo_Api_Request_Platform(self::STATUSES_URL, 'GET') ;
		
		$platform->add_rule('source', 'string', true) ;
		$platform->add_rule('id', 'int64', true) ;
		$platform->add_rule('is_encoded', 'int', false) ;
		
		return $platform ;
	}
	
	public static function get_deserve_products() {
		$url = self::URL . '/plan/deserve/list' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('title', 'string', false) ;
		$platform->add_rule('seller_id', 'int64', false) ;
		$platform->add_rule('seller_name', 'string', false) ;
		$platform->add_rule('category', 'int', false) ;
		$platform->add_rule('limit', 'int', false) ;
		$platform->add_rule('offset', 'int', false) ;
		$platform->add_rule('sign', 'string', true) ;
		return $platform ;
	}
	
	public static function get_ads() {
		$url = self::URL . '/ad/list' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('site', 'int', false) ;
		$platform->add_rule('ad_id', 'int64', false) ;
		$platform->add_rule('ad_desc', 'string', false) ;
		$platform->add_rule('show_status', 'int', false) ;
		$platform->add_rule('limit', 'int', false) ;
		$platform->add_rule('offset', 'int', false) ;
		$platform->add_rule('sign', 'string', true) ;
		return $platform ;
	}
	
	public static function get_ad_sites() {
		$url = self::URL . '/ad/site' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('site_id', 'int', false) ;
		$platform->add_rule('sign', 'string', true) ;
		return $platform ;
	}
	
	public static function get_topics() {
		$url = self::URL . '/topic/list' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('id', 'int', false) ;
		$platform->add_rule('name', 'string', false) ;
		$platform->add_rule('status', 'int', false) ;  //0：下线  1、上线
		$platform->add_rule('limit', 'int', false) ;
		$platform->add_rule('offset', 'int', false) ;
		$platform->add_rule('sign', 'string', true) ;
		return $platform ;
	}
	
	public static function get_topic_info() {
		$url = self::URL . '/topic/get' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('topic_ids', 'int', true) ;
		$platform->add_rule('sign', 'string', true) ;
		return $platform ;
	}
	
	public static function get_products_by_topicid() {
		$url = self::URL . '/plan/topic/get' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false);
	
		$platform->add_rule('topic_id', 'int', true) ;
		$platform->add_rule('limit', 'int', false) ;
		$platform->add_rule('offset', 'int', false) ;
		$platform->add_rule('sign', 'string', true) ;
		return $platform ;
	}
	
	public static function add_motion() {
		$url = self::URL . '/plan/add' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false) ;
		
		$platform->add_rule('title', 'string', true) ;
		$platform->add_rule('description', 'string', false) ;
		$platform->add_rule('price', 'float', false) ;
		$platform->add_rule('discount_price', 'float', true) ;
		$platform->add_rule('discount_percent', 'int', false) ;
		$platform->add_rule('stock', 'int', true) ;
		$platform->add_rule('category', 'int', true) ;
		$platform->add_rule('url', 'string', true) ;
		$platform->add_rule('pic', 'string', false) ;
		$platform->add_rule('mid', 'string', false) ;
		$platform->add_rule('op_id', 'int', true) ;
		$platform->add_rule('start_time', 'int', true) ;
		$platform->add_rule('end_time', 'int', true) ;
		$platform->add_rule('from_id', 'int', true) ;
		$platform->add_rule('app_id', 'int', true) ;
		$platform->add_rule('seller_id', 'int', true) ;
		$platform->add_rule('is_prior', 'int', false) ;
		$platform->add_rule('url_h5', 'string', false) ;
		$platform->add_rule('phone', 'string', false) ;
		$platform->add_rule('sign', 'string', true) ;
		
		return $platform;
	}
	
	public static function get_notice() {
		$url = self::URL . '/notice/detail' ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'GET') ;
		$platform->set_check_succ_ret(false) ;
		
		$platform->add_rule('admin_id', 'int', false) ;
		$platform->add_rule('platform_id', 'int', false) ;
		$platform->add_rule('sign', 'string', true) ;
		
		return $platform;
	}
}
