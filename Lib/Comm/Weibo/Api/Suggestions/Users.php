<?php
class Comm_Weibo_Api_Suggestions_Users {
    const RESOURCE = "suggestions/users";
    
    /**
     * 把某人标志为不感兴趣的人
     */
    public static function users_not_interested(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "not_interested");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "POST");
        $platform->add_rule("uid", "int64",TRUE);
        $platform->add_rule("trim_status", "int");
        return $platform;
    }
    
    /**
     * 获取当前登录用户可能感兴趣的人
     */
    public static function may_interested() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "may_interested");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
        $platform->support_pagination();
        return $platform;
    } 
    
    /**
     * 根据一段微博正文推荐相关微博用户
     */
    public static function users_by_status() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "by_status");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
        
        $platform->add_rule('content', 'string', TRUE);
        $platform->add_rule('num', 'int', FALSE);
        $platform->add_rule('url', 'string', FALSE);
        
        return $platform;
    }
    
    /**
     * 根据当当前登录用户所查看的用户，获取给当前登录用户的推荐关注 
     */
    public static function worth_follow() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "worth_follow");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
        
        $platform->add_rule('uid', 'int64', TRUE);
        $platform->support_pagination();
        
        return $platform;
    }
}