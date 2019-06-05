<?php
/**
 * 表态相关接口SDK
 *
 * @package
 * @copyright  copyright(2013) weibo.com all rights reserved
 * @author     liuyu6<liuyu6@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Attitudes {
    const RESOURCE = 'attitudes';
    
    /**
     * 根据微博消息ID返回该微博表态信息
     * 
     * @return mixed
     */
    public static function show() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_cursor();
        $request->support_pagination();
        $request->add_rule('id', 'int64', true);
        $request->add_rule('base_app', 'int', false);
        $request->add_rule('filter_by_author', 'int', false);
        $request->add_rule('filter_by_source', 'int', false);
        $request->add_rule('type', 'string', false);
        $request->set_request_timeout(2000, 2000);
        return $request;
    }

    /**
     * 我收到的表态列表,已表态为维度
     * 
     * @return mixed
     */
    public static function to_me() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'to_me'); //to_me
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('filter_by_source', 'int', false);
        $request->add_rule('filter_by_author', 'int', false);
        $request->add_rule('type', 'string', false);
        $request->support_cursor();
        $request->support_pagination();
        $request->set_request_timeout(3000, 3000);
        return $request;
    }

    /**
     * 批量获取微博IDS对应的表态计数（支持简版表态计数，type=heart）
     * 
	 * ！重要！该函数只为2012-10-15上线的A-B test功能提供服务，请勿在其它业务中调用
     * @return mixed
	 * @author 孙齐 <sunqi2@staff.sina.com.cn>
	 */
    public static function counts() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'counts');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('ids', 'string', true);
        $request->add_rule('type', 'string', false);
        // TODO A-B test使用，接口非Feed级别性能
        $request->set_request_timeout(2000, 2000);
        return $request;
    }

    /**
     * 收到的表态列表，以微博为维度做合并
     * Enter description here ...
     */
    public static function to_me_status() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'to_me_status'); //to_me
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('filter_by_source', 'int', false);
        $request->support_cursor();
        $request->support_pagination();
        $request->set_request_timeout(3000, 3000);
        return $request;
    }

    /**
     * 对一条微博发表或更新一条表态
     */
    public static function create() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'create');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('attitude', 'string', true);
        $request->add_rule('id', 'int64', true);
        $request->set_request_timeout(2000, 2000);
        return $request;
    }

    /**
     * 删除一条自己的表态
     */
    public static function destroy() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'destroy');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('attid', 'int64', false);
        $request->add_rule('id', 'int64', false);
        $request->set_request_timeout(2000, 2000);
        return $request;
    }

    public static function clear_unread() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'clear_unread');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('id', 'int64', true);
        return $request;
    }
}