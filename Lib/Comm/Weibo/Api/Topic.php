<?php
/**
 * 话题接口SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng <dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Topic {
	const RESOURCE = "trends";
	/**
	 * 获取某人关注的话题
	 */
	public static function get_trends(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, NULL);
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("uid", "int64", TRUE);
		$platform->support_pagination();
		return $platform;
	}
	
	/**
	 * 判断是否关注某话题
	 */
	public static function is_follow(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "is_follow");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("trend_name", "string", TRUE);
		return $platform;
	}
	
	/**
	 * 返回最近一小时内的热门话题 
	 */
	public static function hourly(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "hourly");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->support_base_app();
		return $platform;
	}
	
	/**
	 * 返回最近一天内的热门话题 
	 */
	public static function daily(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "daily");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->support_base_app();
		return $platform;
	}
	
	/**
	 * 返回最近一周内的热门话题 
	 */
	public static function weekly(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "weekly");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->support_base_app();
		return $platform;
	}
	
	/**
	 * 关注某话题 
	 */
	public static function follow(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "follow");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
		$platform->add_rule("trend_name", "string", TRUE);
		return $platform;
	}
	
	/**
	 * 取消关注的某一个话题 
	 */
	public static function destroy(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "destroy");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "DELETE");
		$platform->add_rule("trend_id", "string", TRUE);
		return $platform;
	}
}