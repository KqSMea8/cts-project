<?php
/**
 * 对象接口SDK
 *
 * @package    
 * @copyright  copyright(2013) weibo.com all rights reserved
 * @author     liuyu6<liuyu6@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Object {
	const RESOURCE = "object";
	const OBJECT_ADD_URL = 'http://i2.api.weibo.com/2/object/add.json'; // wiki无此接口
	const URL_PREFIX = "http://i2.api.weibo.com/2/object";
    const OBJECT_IMPORT_URL = 'http://i2.api.weibo.com/2/object/secure/import_object.json';
    const OBJECT_BIND_URL = 'http://i.api.weibo.com/sinaurl/secure/bind_object.json';
    const OBJECT_IMPORT_SIGN = 'c335de25cf500a7f7f0637a90d18bbff';
	
	public static function assemble_url($interface) {
	    return self::URL_PREFIX . '/' . $interface . '.json';
	}
	/**
	 * 添加一个对象
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function add() {
	    $url = self::assemble_url('add');
	    $platform = new Comm_Weibo_Api_Request_Platform($url, 'POST');
	    $platform->add_rule("object_id", "string", TRUE);
	    $platform->add_rule("object", "string", TRUE);
	    
	    return $platform;
	}

    public static function import(){
        $url = self::OBJECT_IMPORT_URL;
        $platform = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        $platform->add_rule("sign", "string", TRUE);
        $platform->add_rule("url", "string", TRUE);
        $platform->add_rule("object", "string", TRUE);

        return $platform;
    }

    public static function bind(){
        $url = self::OBJECT_BIND_URL;
        $platform = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        $platform->add_rule("url", "string", TRUE);
        $platform->add_rule("object_id", "string", TRUE);

        return $platform;
    }


	/**
	 * 根据ID获取单个对象信息
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function show() {
	    $url = self::assemble_url('show');
	    $platform = new Comm_Weibo_Api_Request_Platform($url, 'GET');
	    $platform->add_rule("object_id", "string", TRUE);
	    
	    return $platform;
	}
	
	/**
	 * 批量获取对象信息
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function show_batch() {
	    $url = self::assemble_url('show_batch');
	    $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("object_ids", "string", TRUE);
	     
	    return $platform;
	}

	/**
	 * 根据ID更新单个对象信息
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function modify(){
		$url = self::assemble_url("modify");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
		$platform->add_rule("object_id", "string", TRUE);
		$platform->add_rule("object", "string", TRUE);
		
		return $platform;
	}
	
	/**
	 * 根据ID判断单个对象信息是否存在
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function exist(){
		$url = self::assemble_url("exist");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		$platform->add_rule("object_id", "string", TRUE);
		
		return $platform;
	}
	
	/**
	 * 按域名获取分配的domain_id
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function get_domain_id() {
		$url = self::assemble_url("get_domain_id");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");		
		$platform->add_rule("domain", "string", TRUE);
		
		return $platform;
	}
	
	/**
	 * 将对象注册激活为一个专页帐号
	 * @return Comm_Weibo_Api_Request_Platform
	 */
	public static function activate_page() {
		$url = self::assemble_url("activate_page");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");		
		$platform->add_rule("nickname", "string", FALSE);
		$platform->add_rule("remark_name", "string", TRUE);
		$platform->add_rule("ptype", "int", FALSE);
		$platform->add_rule("owner_uid", "int64", TRUE);
		$platform->add_rule("object_id", "string", TRUE);
		$platform->add_rule("category1", "int", TRUE);
		$platform->add_rule("category2", "int", TRUE);
		$platform->add_rule("category3", "int", TRUE);
		$platform->add_rule("category4", "int", TRUE);
		$platform->add_rule("category5", "int", TRUE);
		
		return $platform;
	}

    public static function bind_object(){
		//$url = 'http://i.api.weibo.com/sinaurl/secure/bind_object.json';
        $url = 'http://i.api.weibo.com/2/object/secure/import_object.json';
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST");		
		$platform->add_rule("object", "string", FALSE);
		$platform->add_rule("url", "string", FALSE);
		$platform->add_rule("sign", "string", FALSE);
        $platform->add_rule("force_update", "int", FALSE);
        $platform->add_rule("object_id", "string", FALSE);
        return $platform;
    }

}
