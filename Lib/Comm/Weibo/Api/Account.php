<?php
/**
 * 账号接口SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Account {
	const RESOURCE = "account";
	/**
	 * 获取用户基本信息 
	 */
	public static function get_profile_basic(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/basic");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64");
		return $platform;
	}
	
	/**
	 * 获取教育信息
	 */
	public static function get_profile_education(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/education");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64");
		return $platform;
	}
	
	/**
	 * 批量获取教育信息 
	 * @todo 确认uids的类型
	 */
	public static function education_batch(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/education_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uids", "int64");
		return $platform;
	}
	
	/**
	 * 获取职业信息
	 */
	public static function get_profile_career(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/career");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64");
		return $platform;
	}
	
	/**
	 * 批量获取职业信息 
	 */
	public static function profile_career_batch(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/career_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uids", "int64");
		return $platform;
	}
	/**
	 * 获取隐私信息
	 */
	public static function get_privacy(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "get_privacy");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		return $platform;
	}
	
	/**
	 * 批量获取用户的隐私设置
	 */
	public static function get_privacy_batch(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "get_privacy_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uids", "string");
		return $platform;
	}
	/**
	 * 获取所有学校列表 
	 */
	public static function profile_school_list(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/school_list");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("province", "int");
		$platform->add_rule("city", "int");
		$platform->add_rule("area", "int");
		$platform->add_rule("type","int",TRUE);
		$platform->add_rule("capital", "string");
		$platform->add_rule("keyword", "string");
		$platform->add_rule("count", "int");
		return $platform;
	}
	
    /**
	 * 获取当前用户手机绑定状态
	 */
    public static function mobile(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "mobile");
        $platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
        return $platform;
    }
    
    /**
     * 获取用户个性设置
     */
    public static function get_settings(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "settings");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
        return $platform;
    }

    /**
     * 获取当前登录用户的水印设置信息
     */
    public static function watermark() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "watermark");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
        unset($url);
        return $platform;
    }

	/**
	 * 申请添加新学校名称
	 */
	public static function profile_new_school(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/new_school");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("province", "int",TRUE);
		$platform->add_rule("city", "int");
		$platform->add_rule("schooltype","int",TRUE);
		$platform->add_rule("school_name", "string",TRUE);
		return $platform;
	}
	
	/**
	 * 更新用户基本信息
	 */
	public static function update_profile_basic(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/basic");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("screen_name", "string");
		$platform->add_rule("real_name", "string");
		$platform->add_rule("real_name_visible", "int");
		$platform->add_rule("province", "int");
		$platform->add_rule("city", "int");
		$platform->add_rule("birthday", "date");
		$platform->add_rule("birthday_visible", "int");
		$platform->add_rule("qq", "string");
		$platform->add_rule("qq_visible", "int");
		$platform->add_rule("msn", "string");
		$platform->add_rule("msn_visible", "int");
		$platform->add_rule("url", "string");
		$platform->add_rule("url_visible", "int");
		$platform->add_rule("gender", "string");
		$platform->add_rule("credentials_type", "int");
		$platform->add_rule("credentials_num", "string");
		$platform->add_rule("email", "string");
		$platform->add_rule("email_visible", "int");
		$platform->add_rule("lang", "string");
		$platform->add_rule("description", "string");
		$platform->add_rule("_trace", "string");
		return $platform;
	}
	
	/**
	 * 更新用户教育信息 
	 */
	public static function update_profile_education(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/education");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("id", "int64");
		$platform->add_rule("year", "int");
		$platform->add_rule("department", "string");
		$platform->add_rule("visible", "int");
		$platform->add_rule("type", "int");
		$platform->add_rule("school_id", "int");
		$callback_obj = new Comm_Weibo_Api_Account;
		$platform->add_before_send_callback($callback_obj, "id_type_school");
		return $platform;
	}
	/**
	 * 删除用户教育信息
	 */
	public static function delete_profile_education(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/education");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"DELETE");
		$platform->add_rule("id", "int64");
		return $platform;
	}
	
	/**
	 * 更新用户职业信息
	 */
	public static function update_profile_career(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "profile/career");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("id", "string");
		$platform->add_rule("start", "int");
		$platform->add_rule("end", "int");
		$platform->add_rule("department", "string");
		$platform->add_rule("visible", "int");
		$platform->add_rule("province", "int");
		$platform->add_rule("city", "int");
		$platform->add_rule("company", "string");
		$callback_obj = new Comm_Weibo_Api_Account;
		$platform->add_before_send_callback($callback_obj, "id_company");
		return $platform;
	}
		
	/**
	 * 更改账户密码
	 */
	public static function password(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "password");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("old_psw", "string", TRUE);
		$platform->add_rule("new_psw", "string", TRUE);
		return $platform;
	}
	
	/**
	 * 绑定用户手机
	 */
    public static function mobile_update(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "mobile/update");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
        $platform->add_rule("number", "string", TRUE);
        return $platform;
    }
    
    /**
     * 解除手机绑定
     */
    public static function mobile_destroy(){
        
    }
    
    /**
     * 更新用户个性设置(只支持手机)
     */
    public static function settings_update(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "settings/update");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
        $platform->add_rule("short_messages", "int64", TRUE);
        return $platform;
    }
    
    /**
     * 更新用户隐私设置（只支持手机）
     */
    public static function update_privacy(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "update_privacy");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
        $platform->add_rule("mobile", TRUE);
        return $platform;
    }
	
	/**
	 * before_send callback方法
	 */
	public function id_type_school($platform){
		if(!is_null($platform->id)){
			if(!is_null($platform->type) || is_null($platform->school_id)){
				throw new Comm_Exception_Program("update education params error");
			}
		}
		else{
			if(is_null($platform->type) || is_null($platform->school_id)){
				throw new Comm_Exception_Program("type and school_id should be set value");
			}
		}
	}
	public function id_company($platform){
		if(is_null($platform->company) && is_null($platform->id)){
			throw new Comm_Exception_Program("company or id should be set value");
		}
	}
	/**
	 * 获取微号信息批量
	 * */
	public static function weihao_batch() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "weihao_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url, 'GET');
		$platform->add_rule("numbers", 'string', TRUE);
		$platform->add_rule("need_all", 'string', FALSE);
		$platform->add_rule("language", 'string', FALSE);
		return $platform;
	}

    public static function update_ability(){
        $url = 'http://10.75.25.166:7702/2/account/admin/update_ability.json';
        $url = 'http://i2.api.weibo.com/2/account/admin/update_ability.json';
        $platform = new Comm_Weibo_Api_Request_Platform($url, 'POST', 1);
        $platform->add_rule("uids", 'string', TRUE);
        $platform->add_rule("index", 'string', TRUE);
        $platform->add_rule("value", 'string', TRUE);
        return $platform;
    }

}
