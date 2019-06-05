<?php
class Comm_Weibo_Api_Notice {
    const RESOURCE = "notice";
    /**
     * 获取用户通知列表
     * @param int64 $uid
     * @param int $page
     * @param int $count
     * @return obj
     */
    public static function get_list($uid){
        $interface = "$uid/list";
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, $interface,"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"GET");
        $request->support_pagination();
        return $request;
    }
    
    /**
     * 发送一条新通知 
     * @param string $uids
     * @param string $title
     * @param string $content
     */
    public static function send(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "app_send","json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"POST");
        $request->add_rule("uids", "string", TRUE);
        $request->add_rule("title", "string", TRUE);
        $request->add_rule("content", "string", TRUE);
        $request->add_set_callback("uids", "Comm_Weibo_Api_Util", "check_batch_values",array("string",",",1000,1));
        $request->add_set_callback("title", "Comm_Weibo_Api_Notice", "check_title");
        $request->add_set_callback("content", "Comm_Weibo_Api_Notice", "check_content");
        return $request;
    }
    
    /**
     * 发送一条已经存在的通知
     * @param string $uids
     * @param string $notice_id
     */
    public static function send_id($notice_id){
        $interface = "send/:{$notice_id}";
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, $interface,"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"POST");
        $request->add_rule("uids", "string", TRUE);
        $request->add_set_callback("uids", "Comm_Weibo_Api_Util", "check_batch_values",array("string",",",1000,1));
        return $request; 
    }
    
    /**
     * 给微博所有用户广播群发一条通知
     * @param unknown_type $title
     * @param unknown_type $content
     */
    public static function send_all(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "send_all","json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url, "POST");
        $request->add_rule("title", "string", TRUE);
        $request->add_rule("content", "string", TRUE);
        $request->add_set_callback("title", "Comm_Weibo_Api_Notice", "check_title");
        $request->add_set_callback("content", "Comm_Weibo_Api_Notice", "check_content");
        return $request; 
    }
    
    /**
     * 删除一条当前应用发出的通知
     * @param string $notice_id
     */
    public static function delete_notice($notice_id){
        $interface = ":{$notice_id}";
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, $interface,"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"DELETE");
        $request->add_rule("_method", "string");
        return $request;
    } 
    
    /**
     * 更新一条当前应用发出的通知
     * @param string $title
     * @param string $content
     * @param string $notice_id
     */
    public static function update_notice($notice_id){
        $interface = ":{$notice_id}";
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, $interface,"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"POST");
        $request->add_rule("title", "string");
        $request->add_rule("content", "string");
        $request->add_set_callback("title", "Comm_Weibo_Api_Notice", "check_title");
        $request->add_set_callback("content", "Comm_Weibo_Api_Notice", "check_content");
        return $request; 
    }
    
    /**
     * 检查通知标题长度 
     * @param string $title
     */
    public static function check_title($title){
        $title_width = mb_strwidth($title,"utf-8");
        if ($title_width <= 60 && $title_width > 0){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    
    /**
     * 检查通知内容长度
     * @param string $content
     */
    public static function check_content($content){
        $content_width = mb_strwidth($content,"utf-8");
        if ($content_width <= 600 && $content_width > 0){
            return TRUE;
        }
        else{
            return FALSE;
        } 
    }
}