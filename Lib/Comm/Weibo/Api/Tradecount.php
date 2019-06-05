<?php
/**
 * 微博-支付项目        调用平台接口，操作支付列表打点
 * @author      wangdong3@staff.sina.com.cn
 * @copyright   Weibo.com
 *
 */
class Comm_Weibo_Api_Tradecount
{
    const INCRCOUNT  = 'http://i2.api.weibo.com/2/remind/client/ext/incr_count.json';
    const CLEARCOUNT = 'http://i2.api.weibo.com/2/remind/client/ext/clear_count.json';
    const ITEM_ID = 'transaction';

    public static function incr_count($source,$uid, $value = ''){
        $request = new Comm_Weibo_Api_Request_Platform(self::INCRCOUNT, 'POST');
        $request -> add_rule('source', 'string', TRUE);
        $request -> add_rule('uid', 'int64', TRUE);
        $request -> add_rule('item_id', 'string', TRUE);
        $request -> add_rule('value', 'int', TRUE);

        $request->source = $source;
        $request->uid = $uid;
        $request->item_id = self::ITEM_ID;
        $request->value = $value;

        return $request;
    }

    public static function clear_count($source, $uid){
        $request = new Comm_Weibo_Api_Request_Platform(self::CLEARCOUNT, 'POST');
        $request -> add_rule('source', 'string', TRUE);
        $request -> add_rule('uid', 'int64', TRUE);
        $request -> add_rule('item_id', 'string', TRUE);

        $request->source = $source;
        $request->uid = $uid;
        $request->item_id = self::ITEM_ID;

        return $request;
    }



}