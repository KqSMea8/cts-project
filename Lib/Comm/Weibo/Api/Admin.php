<?php
/**
 * 后台接口(仅供后台使用)
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Admin{
    
    const RESOURCE = 'admin';
    
    /**
     * 待审批学校名称列表
     */
    public static function account_new_school_list() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'account/new_school_list');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->support_pagination();
        
        return $request;
    }
    
    /**
     * 后台发送私信
     */
    public static function direct_messages_new() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'direct_messages/new');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        
        $request->add_rule('text', 'string', TRUE);
        $request->add_rule('fuid', 'int64', TRUE);
        $request->add_rule('tuids', 'string', TRUE);
        $request->add_set_callback('tuids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        $request->add_rule('cip', 'string', TRUE);
        $request->add_rule('cname', 'string', TRUE);
        $request->add_rule('fids', 'string', TRUE);
        $request->add_set_callback('fids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ','));
        $request->add_rule('id', 'int64', TRUE);
        
        return $request;
    }
    
    /**
     * 推荐位管理-首页tips
     */
    public static function home_tips() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url('proxy/pub', 'recommend/home_tips');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        unset($url);
        return $request;
    }
    
    /**
     * 根据V用户UID返回用户的客服代表信息
     */
    public static function csr_getCsrByUid() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url('proxy/admin', 'csr/getCsrByUid');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        $request->add_rule('uid', 'int64', TRUE);
        return $request;
    }
}