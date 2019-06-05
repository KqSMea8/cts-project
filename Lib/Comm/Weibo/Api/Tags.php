<?php
/**
 * 标签接口SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Tags {
	const RESOURCE = "tags";
	/**
	 * 返回指定用户的标签列表 
	 */
	public static function get_tags(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, NULL);
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64", TRUE);
		$platform->support_pagination();
		return $platform;
	}
	/**
	 * 批量获取用户标签
	 * @todo uids 的类型有疑问 
	 */
	public static function tags_batch(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "tags_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uids", "int64", TRUE);
		return $platform;
	}
	
	/**
	 * 返回系统推荐的标签列表 
	 */
	public static function suggestions(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "suggestions");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("count", "int");
		return $platform;
	}
	
	/**
	 * 添加用户标签
	 */
	public static function create(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "create");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("tags", "string", TRUE);
		$callback_obj = new Comm_Weibo_Api_Tags;
		$platform->add_set_callback("tags", $callback_obj, "check_tags");
		return $platform;
	}
	
	/**
	 * 删除用户标签
	 */
	public static function destroy(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "destroy");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("tag_id", "int64", TRUE);
		return $platform;
	}
	
	/**
	 * 批量删除用户标签
	 * @todo ids 的类型有疑问 
	 */
	public static function destroy_batch(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "destroy_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("ids", "int64", TRUE);
		return $platform;
	}
	/**
	 * set tags参数的callback 方法
	 */
	public function check_tags($tags){
		if(strlen(trim($tags)) <= 0){
			throw new Comm_Exception_Program("param tags can not be null");
		}
		$tmp = explode(",", $tags);
		foreach ($tmp as $tag){
			if(mb_strwidth($tag, "utf-8") > 14){
				throw new Comm_Exception_Program("{$tag} is too long");
			}
		}
		return $tags;
	}
}