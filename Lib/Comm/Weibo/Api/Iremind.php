<?php
/**
 * 提醒接口sdk
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Iremind {
	const RESOURCE = "iremind";
	
	/**
	 * 清除指定提醒条数
	 */
	public static function set_count(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "set_count","json",NULL, FALSE);
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
		$platform->add_rule("user_id", "int64", TRUE);
		$platform->add_rule("type", "string", TRUE);
		$platform->add_rule("value", "int", TRUE);
		return $platform;
	}
	
	/**
	 * 清楚所有提醒条数 
	 */	
	public static function clear_count(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "clear_count","json",NULL, FALSE);
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
		$platform->add_rule("user_id", "int64", TRUE);
		return $platform;
	}
	
	/**
	 * 获取未读数
	 */
	public static function unread_count(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url("remind", "unread_count","json",NULL, FALSE);
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("user_id", "int64", TRUE);
		return $platform;
	}

	/**
	 * 获取未读数
	 */
	public static function client_unread_count(){
        $url = 'http://i.api.weibo.com/2/remind/client_unread_count.json';
		$platform = new Comm_Weibo_Api_Request_Fastplatform($url, "GET", '', 1000);
		$platform->add_rule("unread_message", "int64", FALSE);
		$platform->add_rule("need_back", "string", FALSE);
		return $platform;
	}
}
