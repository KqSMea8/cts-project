<?php
/**
 * 微博类推荐
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Suggestions_Statuses {
    const RESOURCE = "suggestions/statuses";
    /**
     * 对当前用户的friend_timeline根据兴趣进行重排。支持对前500条微博进行重排。
     */
    public static function statuses_reorder() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "reorder");
        $platform = new Comm_Weibo_Api_Request_Platform($url, "GET");
        unset($url);
        
        $platform->add_rule('section', 'int', TRUE);
        $platform->support_pagination();
        $platform->support_cursor();
        
        return $platform;
    }
}