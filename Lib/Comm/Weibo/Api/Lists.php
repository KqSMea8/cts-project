<?php
/**
 * lists相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Lists{
    const RESOURCE = 'lists';
    
    /**
     * 获取指定用户的LIST列表
     */
    public static function user_own_lists() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'user/own_lists');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', false);
        $request->add_rule('list_type', 'int', false);
        $request->add_rule('cursor', 'int', false);
        
        return $request;
    }
    
    /**
     * 获取用户所创建的分组的名称
     */
    public static function user_own_lists_name() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'user/own_lists_name');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', false);
        $request->add_rule('list_type', 'int', false);
        $request->add_rule('cursor', 'int', false);
        
        return $request;
    }
    
    /**
     * 批量获取指定用户在当前登录用户的私有组中的分组信息 
     */
    public static function user_listed_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'user/listed_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uids', 'string', true);
        $request->add_set_callback('uids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 50));
        
        
        return $request;
    }
    
    /**
     * 列出用户作为成员的所有list列表 
     */
    public static function user_listed() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'user/listed');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', false);
        $request->add_rule('cursor', 'int64', false);
        
        return $request;
    }
    
    /**
     * 列出用户订阅的所有list列表 
     */
    public static function user_subscriptions() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'user/subscriptions');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('cursor', 'int64', false);
        
        return $request;
    }
    
    /**
     * 获取LIST成员的最新微博 ，私有list的列表只能自己可以访问 
     */
    public static function members_timeline() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'members/timeline');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('list_id', 'int64', true);
        $request->support_cursor();
        $request->support_pagination();
        $request->support_base_app();
        $request->add_rule('feature', 'int', false);
        
        return $request;
    }
    
    /**
     * 返回list中所有的成员 
     */
    public static function show_members() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show/members');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('list_id', 'int64', true);
        $request->add_rule('cursor', 'int64', false);
        
        return $request;
    }
    
    /**
     * 返回list中所有的订阅者
     */
    public static function show_subscribers() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show/subscribers');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', false);
        $request->add_rule('list_id', 'int64', true);
        $request->add_rule('cursor', 'int64', false);
        
        return $request;
    }
    
    /**
     * 创建一个新的list，每个用户最多能够创建20个。
     */
    public static function create() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'create');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('name', 'string', true);
        $request->add_rule('mode', 'string', false);
        $request->add_rule('description', 'string', false);
        
        return $request;
    }

    /**
     * 更新分组
     */
    public static function update() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('name', 'string', true);
        $request->add_rule('list_id', 'int64', true);
        $request->add_rule('description', 'string', false);
        
        return $request;
    }
    
    /**
     * 删除分组
     */
    public static function destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('list_id', 'int64', true);
        
        return $request;
    }
    
    /**
     * 添加多个关注人到分组
     * 
     * 每个list最多拥有500个用户。私有列表只能添加自己关注的人
     */
    public static function member_add_users() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'member/add_users');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('uids', 'string', true);
        $request->add_set_callback('uids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        $request->add_rule('list_id', 'int64', true);
        
        return $request;
    }
    
    /**
     * 添加关注人到多个分组 
     */
    public static function member_add_lists() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'member/add_lists');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('list_ids', 'string', true);
        $request->add_set_callback('list_ids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        
        return $request;
    }
    
    /**
     * 添加用户到分组
     */
    public static function member_add() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'member/add');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('list_id', 'int64', true);
        
        return $request;
    }
    
    /**
     * 将用户从分组中删除 
     */
    public static function member_destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'member/destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('list_id', 'int64', true);
        
        return $request;
    }
}