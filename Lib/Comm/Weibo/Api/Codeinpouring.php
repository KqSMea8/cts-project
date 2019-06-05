<?php
/**
 * 卡劵平台
 *
 * @author  	chengmeng
 * @version 	2014-12-22
 * @copyright  	copyright(2014) weibo.com all rights reserved
 *
 */
class Comm_Weibo_Api_Codeinpouring
{
	const IMPORT_URL = 'http://c.weibo.com/interface/external/coupon/importcoupon' ;
	
	public static function import_code()
	{
		$url = self::IMPORT_URL ;
		$platform = new Comm_Weibo_Api_Request_ThirdModule($url, 'POST') ;
		$platform->set_check_succ_ret(false) ;
		
		$platform->add_rule('merchantUid', 'int64', TRUE) ;
		$platform->add_rule('uid', 'int64', TRUE) ;
		$platform->add_rule('title', 'string', TRUE) ;
		$platform->add_rule('num', 'int', FALSE) ;
		$platform->add_rule('price', 'float', FALSE) ;
		$platform->add_rule('picId', 'string', TRUE) ;
		$platform->add_rule('picUrl', 'string', FALSE) ;
		$platform->add_rule('couponNumber', 'string', FALSE) ;
		$platform->add_rule('intro', 'string', FALSE) ;
		$platform->add_rule('thirdUrl', 'string', FALSE) ;
		$platform->add_rule('type', 'int', TRUE) ;
		$platform->add_rule('receiveTime', 'string', TRUE) ;
		$platform->add_rule('expireTime', 'string', TRUE) ;
		$platform->add_rule('winId', 'int', FALSE) ;
		$platform->add_rule('thirdId', 'int', TRUE) ;
		$platform->add_rule('ts', 'int', TRUE) ;
		$platform->add_rule('sign', 'string', TRUE) ;
		
		return $platform;
	}
}