<?php
/**
 * nav相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Nav{
    const RESOURCE = 'nav';
    
    /**
     * 获取导航列表
     */
    public static function nav_list() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'list', 'json', NULL, FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('category_ids', 'string', false);
        $request->add_set_callback('category_ids', 'Comm_Weibo_Api_Util', 'check_batch_values', array('int', ','));
        $request->add_rule('lang', 'string', false);
        
        return $request;
    }
}
