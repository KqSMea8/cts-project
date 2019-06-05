<?php
/**
 * 推荐SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Suggestions {
	const RESOURCE = "suggestions";
	/**
	 * 把某人标志为不感兴趣的人
	 */
	public static function users_not_interested(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "users/not_interested");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
		$platform->add_rule("uid", "int64",TRUE);
		$platform->add_rule("trim_status", "int");
		return $platform;
	}
	
	/**
	 * 返回系统推荐的用户列表
	 */
	public static function favorites_hot() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "favorites/hot");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		return $platform;
	}
	
}