<?php
/**
 * 群组用户SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Groups_Users {
    const RESOURCE = "groups";
    /**
     * 获取用户加入的所有群列表
     */
    public static function joined(){
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "users/joined","json",NULL,FALSE);
        $platform = new Comm_Weibo_Api_Request_Platform($url,"GET");
        $platform->support_base_app();
        $platform->support_pagination();
        $platform->add_rule("nuid", "int64", TRUE);
        return $platform;
    }
    
    /**
     *     批量判断用户是否在某个群中
     * @param int $group_id
     */
    public static function exists($group_id) {
        Comm_Weibo_Api_Util::check_int($group_id);
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, $group_id . '/users/exists',"json",NULL,FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url,"GET");
        $request->add_rule('nuid', 'string', TRUE);
        $request->add_set_callback('nuid', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int', ','));
        $request->support_base_app();
        return $request;
    }
    
}