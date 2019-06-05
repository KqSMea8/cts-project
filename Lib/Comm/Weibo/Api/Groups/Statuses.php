<?php
class Comm_Weibo_Api_Groups_Statuses {
    const RESOURCE = "groups";
    /**
     * 发布微博信息
     * @param int $group_id
     */
    public static function publish($group_id){
        Comm_Weibo_Api_Util::check_int($group_id);
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, $group_id . '/statuses/publish',"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"POST");
        
        $request->add_rule('pic_file', 'filepath', FALSE);
        $request->add_rule('status', 'string', TRUE);
        $request->add_rule('pic_fid', 'string', FALSE);
        $request->add_rule('issync', 'int', FALSE);
        $request->add_rule('created_at', 'int', FALSE);
        $request->support_base_app();
        
        return $request;
    }
    
    /**
     * 获取在所有群@当前用户的微博列表 
     */
    public static function mentions(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/mentions',"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"GET");
        
        $request->support_pagination();
        $request->support_base_app();
        
        return $request;
    }
    
    /**
     * 	获取群内@当前用户的未读微博个数
     */
    public static function mentions_unread() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/mentions/unread',"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"GET");
        
        $request->support_base_app();
        
        return $request;
    }
    
    /**
     * 清空群内@当前用户的未读微博个数
     */
    
    public static function mentions_reset_count() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/mentions/reset_count',"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"GET");
        
        $request->support_base_app();
        
        return $request;
    }
    
    /**
     * 	获取用户在所有群收到的评论列表
     */
    public static function commented() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/commented',"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"GET");
        
        $request->support_pagination();
        $request->add_rule('state', 'string');
        $request->support_base_app();
        
        return $request;
    
    }
    
    /**
     * 	获取当前用户的未读群评论个数
     */
    public static function comments_unread() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/comments/unread',"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"GET");
        
        $request->support_base_app();
        
        return $request;
    }
    
    /**
     * 	清空当前用户的未读群评论个数
     */
    public static function comments_reset_count() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'statuses/comments/reset_count',"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"GET");
        
        $request->support_base_app();
        
        return $request;
    }
    
    
}