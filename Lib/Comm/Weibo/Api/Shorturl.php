<?php
/**
 * 短链接口SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Shorturl {
	const RESOURCE = "short_url";
	/**
	 * 批量获取短链富内容
	 */
	public static function batch_info(){
	    $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "batch_info",'json',NULL,FALSE);
	    $platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
	    $platform->add_rule("url_short", "string", TRUE);
		return $platform;
	}
	
	/**
	 * 长链转短链
	 */
	public static function shorten(array $url_longs){
	    $url_longs_query = implode("&", array_map(create_function('$a', 'return "url_long=$a";'), $url_longs));
	    $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "shorten", 'json', NULL, FALSE, $url_longs_query);
	    $platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		return $platform;
	}
	
	public static function shorten_info(array $url_longs) {
		$url_longs_query = implode("&", array_map(create_function('$a', 'return "url_long=$a";'), $url_longs));
		$url = 'http://i2.api.weibo.com/2/short_url/shorten.json?' . $url_longs_query ;
	
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		return $platform;
	}
	
	/**
	 * 短链转长链
	 */
	public static function expand(array $url_shorts){
	    $url_shorts_query = implode("&", array_map(create_function('$a', 'return "url_short=$a";'), $url_shorts));
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "expand", 'json', NULL, FALSE,$url_shorts_query);
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		return $platform;
	}
}