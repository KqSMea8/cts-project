<?php
/**
 * friendships相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Friendships{
    const RESOURCE = 'friendships';
    
    /**
     * 获取用户关注列表及每个关注用户的最新一条微博
     * 
     * 返回结果按关注时间倒序排列，最新关注的用户排在最前面。
     */
    public static function friends() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'friends');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->uid_or_screen_name();
        $request->support_pagination('cursor');
        $request->support_trim_status();
        
        return $request;
    }
    
    /**
     * 获取共同关注人列表接口
     */
    public static function friends_in_common() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'friends/in_common');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('suid', 'int64', true);
        $request->support_pagination();
        $request->support_trim_status();
        
        return $request;
    }
    
    /**
     * 获取双向关注列表
     */
    public static function friends_bilateral() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'friends/bilateral');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->support_pagination();
        $request->add_rule('sort', 'int', false);
        
        return $request;
    }
    
    /**
     * 获取双向关注ID列表
     */
    public static function friends_bilateral_ids() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'friends/bilateral/ids');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->support_pagination();
        $request->add_rule('sort', 'int', false);
        
        return $request;
    }
    
    /**
     * 获取用户关注对象uid列表 
     */
    public static function friends_ids() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'friends/ids');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->uid_or_screen_name();
        $request->support_pagination('cursor');
        
        return $request;
    }
    
    /**
     * 批量获取当前登录关注人的备注信息 
     */
    public static function friends_remark_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'friends/remark_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uids', 'string', true);
        $request->add_set_callback('uids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 50));
        
        return $request;
    }
    
    /**
     * 获取用户粉丝列表及每个粉丝的最新一条微博 
     */
    public static function followers() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'followers');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->uid_or_screen_name();
        $request->support_pagination('cursor');
        $request->support_trim_status();
        
        return $request;
    }
    
    /**
     * 返回用户的粉丝用户ID列表
     */
    public static function followers_ids() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'followers/ids');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->uid_or_screen_name();
        $request->support_pagination('cursor');
        
        return $request;
    }
    
    /**
     * 获取用户活跃粉丝列表。每次最多返回20条，包括用户的最新的微博 
     */
    public static function followers_active() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'followers/active');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('count', 'int', false);
        
        return $request;
    }
    
    /**
     * 获取当前登录用户的关注人中，关注了指定用户的用户列表
     */
    public static function friends_chain_followers() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'friends_chain/followers');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->support_pagination();
        
        return $request;
    }
    
    /**
     * 获取用户所创建的分组列表
     */
    public static function groups() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'groups');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('show_detail ', 'int', false);
        
        return $request;
    }
    
    /**
     * 获取某个分组的详细信息
     */
    public static function groups_show () {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'groups/show');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('list_id ', 'int64', true);
        
        return $request;
    }
    
    /**
     * 获取两个用户关系的详细情况
     */
    public static function show() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $names_of_uid_screen = array(
            array('source_id', 'source_screen_name'),
            array('target_id', 'target_screen_name')
        );
        Comm_Weibo_Api_Util::one_or_other_multi($request, $names_of_uid_screen);
        
        return $request;
    }
    
    /**
     * 获取某用户（无需登录）与一组用户的关注关系
     */
    public static function exists_batch_internal() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'exists_batch_internal');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('uids', 'string', true);
        $request->add_set_callback('uids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        
        return $request;
    }
    
    /**
     * 关注一个用户
     */
    public static function create() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'create');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->uid_or_screen_name();
        
        return $request;
    }
    
    /**
     * 当前登录用户批量关注指定ID的用户 
     */
    public static function create_batch() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'create_batch');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('uids', 'string', true);
        $request->add_set_callback('uids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 20));
        
        return $request;
    }
    
    /**
     * 取消关注某用户
     */
    public static function destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->uid_or_screen_name();
        
        return $request;
    }
    
    /**
     * 移除粉丝
     */
    public static function followers_destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'followers/destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        
        return $request;
    }
    
    /**
     * 更新当前登录用户所关注的某个好友的备注信息
     */
    public static function remark_update() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'remark/update');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('remark', 'string', true);
        
        return $request;
    }
    
    /**
     * 调整用户的分组顺序
     */
    public static function groups_order() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'groups/order');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('list_ids', 'string', true);
        $request->add_set_callback('list_ids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        $request->add_rule('count', 'int', true);
        
        return $request;
    }
	/**
	 * 获取密友数 
	 */
	public static function close_friends_counts() {
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'close_friends/counts');
		$request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        $request->add_rule('uid', 'int64', true);
		unset($url);

		return $request;
	}

}
