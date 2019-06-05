<?php
/**
 * 订阅相关接口
 *
 * @package    api
 * @copyright  copyright(2013) weibo.com all rights reserved
 * @author     zhongwei4
 */

class Comm_Weibo_Api_Subscribe {
    const RESOURCE = 'messages';

    public static function subscribe() {
        $url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, 'subscribe');
        $request = new Comm_Weibo_Api_Request_Platform($url, 'POST');

        $request->add_rule('source', 'string', true);
        $request->add_rule('uid', 'int64', true);
        $request->add_rule('uid_from', 'int64', true);

        return $request;
    }

}