<?php
/**
 * widget相关接口
 *
 * @package    api
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Widget{
    const RESOURCE = 'widget';
    
    /**
     * 根据短链获取媒体的播放HTML
     */
    public static function show() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'show', 'json', NULL, FALSE);
        $request = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        unset($url);
        
        $request->add_rule('short_url', 'string', true);
        $request->add_rule('lang', 'string', false);
        $request->add_rule('jsonp', 'string', false);
        $request->add_rule('template_name', 'string', false);
        
        return $request;
    }
}