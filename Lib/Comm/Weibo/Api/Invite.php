<?php
/**
 * 邀请接口SDK
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Invite {
    const RESOURCE = 'invite';
    
    /**
     * 发送邀请
     */
    public static function send(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'send', 'json', NULL, FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,'POST');
        
        $request->add_rule('uid', 'int64', TRUE);
        $request->add_rule('to_uids', 'string', TRUE);
        $request->add_set_callback('to_uid', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 5));
        $request->add_rule('type', 'string', FALSE);
        $request->add_rule('value', 'int', FALSE);
        $request->add_before_send_callback('Comm_Weibo_Api_Invite', "check_send_value", array($request));
        $request->add_rule('content', 'string', FALSE);
        $request->add_rule('question', 'int', FALSE);
        $request->add_rule('answer', 'string', FALSE);
        
        return $request;
    }
    
    /**
     * 获取某个用户邀请隐私 
     */
    public static function privacy_get() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'privacy/get', 'json', NULL, FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,'GET');
        
        $request->add_rule('uid', 'int64', TRUE);
        $request->add_rule('type', 'string', FALSE);
        
        return $request;
    }
    
    /**
     * 检查邀请隐私
     */
    public static function check() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'check', 'json', NULL, FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,'POST');
        
        $request->add_rule('uid', 'int64', TRUE);
        $request->add_rule('to_uids', 'string', TRUE);
        $request->add_rule('type', 'string', FALSE);
        $request->add_rule('value', 'int', FALSE);
        $request->add_before_send_callback('Comm_Weibo_Api_Invite', "check_send_value", array($request));
        
        return $request;
    }
    
    public static function check_send_value($request) {
        if(!is_null($request->type) && $request->type == 'game') {
            if(is_null($request->value)) {
                throw new Comm_Exception_Program('the parameter value must be set!');
            }
        }
    }
}