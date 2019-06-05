<?php
/**
 * Comm_Weibo_Api_Remind
 * 微博打点接口
 * @author    shengfu<shengfu@staff.weibo.com>
 * @created   2015/6/17
 * @copyright copyright(2013) weibo.com all rights reserved
 * http://wiki.intra.weibo.com/2/remind/client/ext/incr_count
 */
class Comm_Weibo_Api_Remind
{

    const RESOURCE = "remind";
    const URL_PREFIX = "http://i.api.weibo.com/2/remind";

    public static function assemble_url($interface) {
        return self::URL_PREFIX . '/' . $interface . '.json';
    }

    /**
     * 添加打点(累加计数方式)
     * @return Comm_Weibo_Api_Request_Platform
     */
    public static function incr_count () {
        $url = self::assemble_url('client/ext/incr_count');
        $platform = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        $platform->add_rule("source", "string", TRUE);
        $platform->add_rule("uid", "int64", FALSE);
        $platform->add_rule("item_id", "string", TRUE);
        $platform->add_rule("value", "int", TRUE);

        return $platform;
    }

    /**
     * 获取未读记录数
     * @return Comm_Weibo_Api_Request_Platform
     */
    public static function unread_count () {
        $url = self::assemble_url('client_unread_count');
        $platform = new Comm_Weibo_Api_Request_Platform($url, 'GET');
        $platform->add_rule("source", "string", TRUE);
        $platform->add_rule("unread_message", "int", FALSE);
        $platform->add_rule("need_back", "string", FALSE);
        return $platform;
    }

    /**
     * 消点
     * @return Comm_Weibo_Api_Request_Platform
     */
    public static function clear_count () {
        $url = self::assemble_url('client/ext/clear_count');
        $platform = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        $platform->add_rule("source", "string", TRUE);
        $platform->add_rule("uid", "int64", FALSE);
        $platform->add_rule("item_id", "string", TRUE);
        return $platform;
    }

    //全量打点接口
    public static function countAll() {
        $url = 'http://i.api.weibo.com/2/remind/client/ext/batch_incr_count.json';
        $platform = new Comm_Weibo_Api_Request_Platform($url, 'POST');
        $platform->add_rule("source", "string", TRUE);
        $platform->add_rule("uid", "int64", FALSE);
        $platform->add_rule("item_id", "string", TRUE);
        $platform->add_rule("value", "int64", TRUE);
        $platform->add_rule("dot_type", "int64", TRUE);
        return $platform;
    }
}
