<?php
/**
 * blocks相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Blocks{
    const RESOURCE = 'blocks';
    
    /**
     * 检测指定用户是否在黑名单内 
     */
    public static function exists() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'exists');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        
        return $request;
    }
    
    /**
     * 获取黑名单列表
     */
    public static function blocking() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'blocking');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        
        return $request;
    }
    
    /**
     * 获取黑名单ID列表
     */
    public static function blocking_ids() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'blocking/ids');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        
        return $request;
    }
    
    /**
     * 将某用户加入登录用户的黑名单 
     */
    public static function create() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'create');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        
        return $request;
    }
    
    /**
     * 将某用户从当前登录用户的黑名单中移除 
     */
    public static function destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'DELETE');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        
        return $request;
    }

}