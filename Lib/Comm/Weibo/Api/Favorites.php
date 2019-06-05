<?php
/**
 * favorites相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Favorites {
    const RESOURCE = 'favorites';

    /**
     * 获取当前用户的收藏列表 
     */
    public static function favorites() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, '');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('page', 'int');
        $request->add_rule('count','int');
        return $request;
    }
    
    /**
     * 返回指定收藏的信息
     */
    public static function show() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('id', 'int64', TRUE);
        
        return $request;
    }
    
    /**
     * 根据标签返回当前用户该标签下的所有收藏
     */
    public static function by_tags() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'by_tags');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('tid', 'int64', TRUE);
        $request->support_pagination();
        
        return $request;
    
    }
    
    /**
     * 当前登录用户的收藏标签列表
     */
    public static function tags() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'tags');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        
        return $request;
    }
    
    /**
     * 常用标签列表
     */
    public static function tags_common() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'tags/common');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        $request->add_rule('id', 'int64', true);
        
        return $request;
    }
    
    /**
     * 添加收藏
     */
    public static function create() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'create');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('id', 'int64');
        
        return $request;
    }
    
    /**
     * 删除微博收藏。注意：只能删除自己收藏的信息。
     */
    public static function destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('id', 'int64');
        
        return $request;
    }
    
    /**
     * 批量删除收藏 
     */
    public static function destroy_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('ids', 'string', true);
        $request->add_set_callback('ids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        
        return $request;
    }
    
    /**
     * 更新收藏标签
     */
    public static function tags_update() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'tags/update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('id', 'int64', true);
        $request->add_rule('tags', 'string', false);
        $request->add_set_callback('tags', 'Comm_Weibo_Api_Util', 'check_batch_values', array('string', ',', 2));
        
        return $request;
    }
    
    /**
     * 更新当前用户所有收藏下的指定标签
     */
    public static function tags_update_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'tags/update_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('tid', 'int64', true);
        $request->add_rule('tag', 'string', false);
        
        return $request;
    }
    
    /**
     * 删除指定标签（即删除当前用户所有收藏中的此标签）
     */
    public static function tags_destroy_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'tags/destroy_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('tid', 'int64', true);
        
        return $request;
    }
    
}