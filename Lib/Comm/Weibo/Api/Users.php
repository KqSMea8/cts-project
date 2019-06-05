<?php
/**
 * 用户接口SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Users {
	const RESOURCE = "users";
	/**
	 * 根据用户ID获取用户资料
	 */
	public static function show(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "show");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->uid_or_screen_name();
		$platform->add_rule("has_extend", "int", FALSE);
		return $platform;
	}
	
	/**
	 * 通过个性域名获取用户信息
	 */
	public static function domain_show(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "domain_show");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("domain", "string", TRUE);
		$platform->add_rule("has_extend", "int", FALSE);
		return $platform;
	}
	
	/**
	 * 批量获取用户信息 
	 */
	public static function show_batch(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "show_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->uid_or_screen_name("uids","screen_name",TRUE);
		$platform->add_rule("has_extend", "int", FALSE);
		$platform->add_rule("trim_status", "int", 0);
		$platform->add_rule("simplify", "string", FALSE);
		return $platform;
	}
	
	/**
	 * 获取系统推荐用户
	 */
	public static function hot(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "hot");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("category", "string");
		return $platform;
	}
	
	/**
	 * 获取用户可能感兴趣的人
	 */
	public static function may_interested(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "may_interested");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("type", "int");
		return $platform;
	}
	
	/**
	 * 通过一批UID获取用户的扩展信息.仅内部使用
	 */
	public static function show_extend() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "show_extend");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		
        Comm_Weibo_Api_Util::one_or_other_multi($platform);
		
		return $platform;
	}
	
	/**
	 * 获取用户版本信息
	 */
	public static function get_version() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "get_version");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		
		$platform->add_rule("uid", "int64", TRUE);
		
		return $platform;
	}
    
	/**
	 * 批量获取用户的关注数、粉丝数、微博数
	 */
    public static function counts() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "counts");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
        $platform->add_rule("uids", "string", TRUE);
        return $platform;
    }
    
	/**
     * 
     * 获取用户的类型
     * @param int64 $uid
     */
	public static function get_user_type(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "state");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("uid", "int64", TRUE);
		return $platform;
	}

	/**
     * 屏蔽某用户的feed
     * Enter description here ...
     */
	public static function block_user(){
    	$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "filter/create");
        $platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
        $platform->add_rule("uid", "int64",true);
        return $platform;
	}
	
	/**
	 * 判断是否为屏蔽用户
	 * Enter description here ...
	 */
	public static function is_block_user() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "is_filtered");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule('uids', 'string', TRUE);
		return $platform;		
	}

}