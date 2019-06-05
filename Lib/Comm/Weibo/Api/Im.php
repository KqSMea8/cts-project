<?php
/**
 * im接口SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Im{
	const RESOURCE = "im";
	/**
	 * 当前登录用户设置自己的隐身状态 
	 */
	public static function set_privacy(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "account/set_privacy");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("uid", "int64", TRUE);
		$platform->add_rule("privacy", "int");
		return $platform;
	}
	
	/**
	 * 当前用户查询自己隐身设置状态 
	 */
	public static function query_privacy(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "status/query_privacy");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64", TRUE);
		return $platform;
	}
	
	/**
	 * 查询用户在线状态
	 */
	public static function status_query(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "status/query");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64", TRUE);
		$platform->add_rule("is_sample", "int");
		return $platform;
	}
	
	/**
	 * 批量查询用户的在线状态
	 */
	public static function status_query_batch(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "status/query_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64", TRUE);
		$platform->add_rule("is_sample", "int");
		return $platform;
	}
	/**
	 * 查询当前用户的最近联系人
	 */
	public static function roster_recent_contacts(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "roster/recent_contacts");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		return $platform;
	}
	
	/**
	 * 查询当前用户好友列表
	 */
	public static function roster_friends(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "roster/friends");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("cursor", "int");
		$platform->support_pagination();
		return $platform;
	}
	
	/**
	 * 搜索好友
	 */
	public static function roster_search(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "roster/roster_search");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64", TRUE);
		$platform->add_rule("words", "string", TRUE);
		return $platform;
	}
}