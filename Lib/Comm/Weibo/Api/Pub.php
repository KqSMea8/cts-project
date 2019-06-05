<?php
/**
 * 运营相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Pub{
    
    const RESOURCE = 'pub';
    
    /**
     * 首页发布器右上角话题
     */
    public static function recommend_issue_topic(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'recommend/issue_topic');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        return $request;
    }
    
    /**
     * 首页右侧热点话题
     */
    public static function recommend_top_topics() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'recommend/top_topics');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('pid', 'int', TRUE);
        $request->add_rule('cid', 'int', TRUE);
        $request->add_rule('num', 'int', TRUE);
        
        return $request;
    }
}