<?php
/**
 * 游戏相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Game{
    const RESOURCE = 'proxy/game';
    
    /**
     * 获取用户的游戏列表
     */
    public static function games(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'games');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        
        return $request;
    }
    
    /**
     * 获取推荐游戏
     */
    public static function games_suggestions(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'games/suggestions');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        return $request;
    }
    
    /**
     * 获取热门游戏
     */
    public static function games_hot(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'games/hot');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        return $request;
    }
    
}