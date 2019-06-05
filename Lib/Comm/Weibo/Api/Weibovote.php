<?php
/**
 * 微博投票接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_WeiboVote {
    const RESOURCE = "weibonew";
    
    /**
     * 获取投票
     */
    public static function detail() {
        $url = Comm_Weibo_Api_Request_Vote::assemble_vote_api_url(self::RESOURCE);
        $request = new Comm_Weibo_Api_Request_Vote($url, "POST", 'detail');
        unset($url);
        
        $request->support_cuid();
        $request->add_rule("poll_id", "int", TRUE);
        $request->add_rule("ptype", "int", false);
        $request->add_rule("sh", "int", false);
        $request->support_from();
        
        return $request;
    }
    
    /**
     * 获取投票列表
     */
    public static function mylist() {
        $url = Comm_Weibo_Api_Request_Vote::assemble_vote_api_url(self::RESOURCE);
        $request = new Comm_Weibo_Api_Request_Vote($url, "POST", 'mylist');
        unset($url);
        
        $request->support_cuid();
        $request->add_rule("page", "int", TRUE);
        $request->add_rule("count", "int", TRUE);
        $request->support_from();
        
        return $request;
    }
    
    /**
     * 获取投票创建页面
     */
    public static function create() {
        $url = Comm_Weibo_Api_Request_Vote::assemble_vote_api_url(self::RESOURCE);
        $request = new Comm_Weibo_Api_Request_Vote($url, "POST", 'create');
        unset($url);
        
        $request->support_from();
        
        return $request;
    }
    
    /**
     * 获取投票标题和发起人信息
     */
    public static function summary() {
        $url = Comm_Weibo_Api_Request_Vote::assemble_vote_api_url(self::RESOURCE);
        $request = new Comm_Weibo_Api_Request_Vote($url, "POST", 'summay');
        unset($url);
        
        $request->add_rule('poll_id', 'int', TRUE);
        
        return $request;
    }
    
    /**
     * 创建投票
     */
    public static function submit_create() {
        $url = Comm_Weibo_Api_Request_Vote::assemble_vote_api_url(self::RESOURCE);
        $request = new Comm_Weibo_Api_Request_Vote($url, "POST", 'submit_create');
        unset($url);
        
        $request->support_cuid();
        $request->add_rule('title', 'string', TRUE);
        $request->add_rule('pid', 'string', FALSE);
        $request->add_rule('info', 'string', FALSE);
        $request->add_rule('vote_result', 'int', TRUE);
        $request->add_rule('num', 'int', TRUE);
        $request->add_rule('ip', 'string', TRUE);
        $request->add_rule('date', 'string', TRUE);
        $request->add_rule('hh', 'string', TRUE);
        $request->add_rule('mm', 'string', TRUE);
        $request->add_rule('items', 'string', TRUE);
        $request->add_set_callback('items', 'Comm_Weibo_Api_Util', 'check_batch_values', array('string', ','));
        $request->add_rule('verified', 'int', FALSE);
        $request->support_from();
        
        return $request;
    }
    
    /**
     * 参与投票
     */
    public static function joined() {
        $url = Comm_Weibo_Api_Request_Vote::assemble_vote_api_url(self::RESOURCE);
        $request = new Comm_Weibo_Api_Request_Vote($url, "POST", 'joined');
        unset($url);
        
        $request->support_cuid();
        $request->add_rule('poll_id', 'int', TRUE);
        $request->add_rule('item_id', 'string', TRUE);
        $request->add_set_callback('item_id', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int', ','));
        $request->add_rule('anonymous', 'int', FALSE);
        $request->add_rule('share', 'int', FALSE);
        $request->add_rule('verified', 'int', FALSE);
        
        return $request;
    }
}