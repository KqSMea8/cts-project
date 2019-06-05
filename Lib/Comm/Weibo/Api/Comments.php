<?php
/**
 * comments相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Comments{
    const RESOURCE = 'comments';
    
    /**
     * 根据微博消息ID返回某条微博消息的评论列表的。 
     */
    public static function show() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_cursor();
        $request->support_pagination();
        $request->add_rule('id', 'int64', true);
        $request->add_rule('filter_by_author', 'int', false);
        
        return $request;
    }
    
    /**
     * 我发出的评论列表 
     */
    public static function by_me() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'by_me');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        $request->add_rule('filter_by_source', 'int', false); 
        $request->support_cursor();
        $request->support_pagination();
        
        return $request;
    }
    /**
     *   我收到的评论列表
     */
    public static function to_me() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'to_me');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        $request->add_rule('filter_by_source', 'int', false); 
        $request->support_cursor();
        $request->support_pagination();
        $request->add_rule('filter_by_author', 'int', false);
        
        return $request;
    }
    /**
     * 获取当前用户发送及收到的评论列表 
     */
    public static function timeline() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_cursor();
        $request->support_pagination();
        $request->support_trim_user();
        
        return $request;
    }
    
    /**
     * 返回最新n条提到登录用户的评论
     */
    public static function mentions() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'mentions');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_cursor();
        $request->support_pagination();
        $request->add_rule('filter_by_author', 'int', false);
        $request->add_rule('filter_by_source', 'int', false);
        
        return $request;
    }
    
    /**
     * 根据批量评论ID返回评论信息
     */
    public static function show_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('cids', 'string', true);
        $request->add_set_callback('cids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 50));
        
        return $request;
    }
    
    /**
     * 评论一条微博
     */
    public static function create() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'create');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('comment', 'string', true);
        $request->add_rule('id', 'int64', true);
        $request->add_rule('comment_ori', 'int', false);
        $request->add_rule('skip_check', 'int');
        
        return $request;
    }
    /**
     * 删除一条微博
     */
    public static function destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('cid', 'int64', true);
        
        return $request;
    }
    /**
     * 批量删除微博
     */
    public static function destroy_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('cids', 'string', true);
        $request->add_set_callback('cids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 20));
        
        return $request;
    }
    /**
     * 回复一条微博
     */ 
    public static function reply() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'reply');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('cid', 'int64', true);
        $request->add_rule('id', 'int64', true);
        $request->add_rule('comment', 'string', true);
        $request->add_rule('without_mention', 'int', false);
        $request->add_rule('comment_ori', 'int', false);
        $request->add_rule('skip_check', 'int');
        
        return $request;
    }
}