<?php
/**
 * 对象喜欢接口SDK
 *
 * @package    
 * @copyright  copyright(2013) weibo.com all rights reserved
 * @author     liuyu6<liuyu6@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Likes {
	const RESOURCE = "likes";
	const URL_PREFIX = "http://i2.api.weibo.com/2/likes";
	
	public static function assemble_url($interface) {
	    return self::URL_PREFIX . '/' . $interface . '.json';
	}
	
	/**
	 * 喜欢某个对象
	 * @see http://wiki.intra.weibo.com/2/likes/update
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function update(){
		$url = self::assemble_url("update");
		
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");		
		$platform->add_rule("object_id", "string", TRUE);
		$platform->add_rule("object_type", "string", TRUE);
		$platform->add_rule("with_activitie", "int", FALSE);
		$platform->add_rule("visible", "int", FALSE);
		$platform->add_rule("lat", "float", FALSE);
		$platform->add_rule("long", "float", FALSE);
		$platform->add_rule("content", "string", FALSE);
		
		return $platform;
	}
	
	/**
	 * 取消喜欢某个对象
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function destroy(){
		$url = self::assemble_url("destroy");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
		$platform->add_rule("object_id", "string", TRUE);
		
		return $platform;
	}
	
	/**
	 * 根据ID批量获取对象的总喜欢数 
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function counts(){
		$url = self::assemble_url("counts");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("object_ids", "string", TRUE);
		
		return $platform;
	}
	
	/**
	 * 根据ID获取某个对象喜欢过的人及其喜欢动态列表
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function lists(){
		$url = self::assemble_url("list");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("object_id", "string", TRUE);
		$platform->add_rule("friendships_range", "int", FALSE);
		$platform->add_rule("page", "int", FALSE);
		$platform->add_rule("count", "int", FALSE);
		
		return $platform;
	}
	
	/**
	 * 判断某个人是否喜欢过某个对象
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function exist(){
		$url = self::assemble_url("exist");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("uid", "int64", TRUE);
		$platform->add_rule("object_id", "string", TRUE);
		
		return $platform;
	}
	
	/**
	 * 批量判断某个人是否喜欢过某个对象
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function exists() {
		$url = self::assemble_url("exists");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("uid", "int64", FALSE);
		$platform->add_rule("object_ids", "string", TRUE);
		
		return $platform;
	}
	
	/**
	 * 我发出的喜欢列表
	 */
	/**
	 * 
	 */
	public static function by_me() {
		$url = self::assemble_url("by_me");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("uid", "int64", FALSE);
		$platform->add_rule("object_type", "string", TRUE);
		$platform->add_rule("page", "int", FALSE);
		$platform->add_rule("count", "int", FALSE);
		$platform->add_rule("is_encoded", "int", FALSE);
		
		return $platform;
	}
	
	/**
	 * 我发出的喜欢列表的ID
	 * @return Comm_Weibo_Api_Request_Platform
	 */
    public static function by_me_ids() {
        $url = self::assemble_url("by_me/ids");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");       
        $platform->add_rule("uid", "int64", FALSE);
        $platform->add_rule("object_type", "string", TRUE);
        $platform->add_rule("page", "int", FALSE);
        $platform->add_rule("count", "int", FALSE);
        
        return $platform;
    }
    
    /**
     * 我发出的喜欢的总计数
     * @return Comm_Weibo_Api_Request_Platform
     */
	public static function by_me_count(){
		$url = self::assemble_url("by_me/count");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("uid", "int64", FALSE);
        $platform->add_rule("object_type", "string", TRUE);
        
		return $platform;
	}

    public static function liker_list()
    {
        $url = 'http://i2.api.weibo.com/2/darwin/product/liker_list.json';
        //$url = 'http://10.229.13.66/2/darwin/product/liker_list.json';
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
        $platform->add_rule('from_id', 'string', FALSE);
        $platform->add_rule('pid', 'string', FALSE);
        $platform->add_rule('since_id', 'int64', FALSE);
        $platform->add_rule('count', 'int64', FALSE);
        $platform->add_rule('filter', 'int64', FALSE);
    
        return $platform;
    }
}