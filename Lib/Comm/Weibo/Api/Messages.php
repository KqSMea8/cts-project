<?php 
/**
 * 私信接口SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng <dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Messages {
	const RESOURCE = "direct_messages";
	/**
	 * 
	 * 获取某个用户最新的私信列表
	 */
	public static function get_new_list(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, NULL);
		$plateform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$plateform->support_cursor();
		$plateform->support_pagination();
		return $plateform;
	}
	
	/**
	 * 获取当前用户发送的最新私信列表
	 */
	public static function send_list(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "sent");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->support_cursor();
		$platform->support_pagination();
		return $platform;
	}
	
	/**
	 * 获取与当前登录用户有私信往来的用户列表
	 */
	public static function user_list(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "user_list");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("count", "int");
		$platform->add_rule("cursor", "int");
		return $platform;
	}

	/**
	 * 
	 * 获取与指定用户的往来私信列表
	 */
    public static function conversation(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "conversation");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$platform->add_rule("uid", "int64", TRUE);
		$platform->support_cursor();
		$platform->support_pagination();
		return $platform;
	}
	
	/**
	 * 
	 * 获取与指定用户的往来私信列表(i.t接口)
	 */
    public static function getchatmessage($cip){
	    $url = Comm_Weibo_Api_Request_IDotT::assemble_url('', 'message', 'getchatmessage', $cip);
	    
	    $idott = new Comm_Weibo_Api_Request_IDotT($url, "GET");
	    $idott->add_rule("fuid", "int64", TRUE);
	    $idott->add_rule("page", "int", FALSE);
	    $idott->add_rule("pagesize", "int", FALSE);
	    
	    return $idott;
	}
	
	
	/**
	 * 获取与当前登录用户有私信往来的用户列表(i.t接口)
	 * @param 当前用户IP $cip
	 */
	public static function get_message_list($cip) {
	    $url = Comm_Weibo_Api_Request_IDotT::assemble_url('', 'message', 'getmessagelist', $cip);
	    
	    $idott = new Comm_Weibo_Api_Request_IDotT($url, "GET");
	    $idott->add_rule("fuid", "int64", TRUE);
	    $idott->add_rule("page", "int", FALSE);
	    $idott->add_rule("pagesize", "int", FALSE);
	    
	    return $idott;
	}
	/*
	 * 发一条私信
	 */
	public static function new_message(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url('2/' . self::RESOURCE, "new");
        //$url = "http://10.73.14.122:16888/2/direct_messages/new.json";
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST", 1);
		$call_back_obj = new Comm_Weibo_Api_Messages;
		$platform->uid_or_screen_name();
		$platform->add_rule("text", "string", TRUE);
		$platform->add_rule("fids", "string", FALSE);
		$platform->add_rule("id", "int64", FALSE);
		$platform->add_rule('skip_check', 'int');
		return $platform;
	}

    /*
     * add by zhongwei4
	 * 发一条私信, new.json
	 */
    public static function new_json_message(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url('2/' . self::RESOURCE, "new");
        //$url = "http://10.73.14.122:16888/2/direct_messages/new.json";
        $platform = new Comm_Weibo_Api_Request_Platform($url, "POST", 1);
        //$call_back_obj = new Comm_Weibo_Api_Messages;
        //$platform->uid_or_screen_name();
        $platform->add_rule("text", "string", TRUE);
        $platform->add_rule("uid", "string", FALSE);
        //$platform->add_rule("id", "int64", FALSE);
        //$platform->add_rule('skip_check', 'int');
        return $platform;
    }
	
	/*
	 * 删除一条私信
	 */
	public static function destroy(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "destroy");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("id", "int64", TRUE);
		return $platform;
	}
	
	/*
	 * 批量删除私信
	 */
	public static function destroy_batch(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "destroy_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"DELETE");
		
        $one_or_other = array(
            array('ids', 'uid'),
        );
        
        Comm_Weibo_Api_Util::one_or_other_multi($platform, $one_or_other);
        $platform->add_set_callback('ids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        $platform->add_set_callback('uid', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        
		return $platform;
	}
	
	/**
	 * 根据私信ID批量获取私信内容
	 */
	public static function show_batch() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "show_batch");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		
		$platform->add_rule("dmids", "string", TRUE);
		$platform->add_set_callback('dmids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 50));
		
		return $platform;
	}
	
	/**
	 * 判断当前登录用户是否可以给对方发私信。 
	 */
	public static function is_capable() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "is_capable");
		$platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
		
		$platform->add_rule("uid", "int64", TRUE);
		
		return $platform;
	}

    /**
     * send_group_msg 
     * 发送群私信
     * @static
     * @return void
     */
	public static function send_group_msg() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url('groupchat', 'send_message');
        //$url = 'http://172.16.234.135:8887/groupchat/send_message.json';
		$platform = new Comm_Weibo_Api_Request_Platform($url, "POST", 1);
		
		$platform->add_rule("id", "int64", TRUE);
		$platform->add_rule("content", "string", TRUE);
		$platform->add_rule("fids", "string", FALSE);//需要发送的附件ID。多个ID时以逗号分隔。上限为10个
		$platform->add_rule("latitude", "string", FALSE);
		$platform->add_rule("longitude", "string", FALSE);
		$platform->add_rule("mblogid", "string", FALSE);//分享的微博id
		$platform->add_rule("ip", "string", FALSE);//用户ip地址
		$platform->add_rule("annotations", "string", FALSE);//扩展字段
		return $platform;
	}
}
