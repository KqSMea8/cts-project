<?php
/**
 * Comm_Weibo_Remind
 * 微博打点接口
 * @author    shengfu<shengfu@staff.weibo.com>
 * @created   2015/6/17
 * @copyright copyright(2013) weibo.com all rights reserved
 * http://wiki.intra.weibo.com/2/remind/client/ext/incr_count
 */
class Comm_Weibo_Remind
{
    const URL_PREFIX = 'http://i.api.weibo.com/2/remind/client/ext';
    const ACTION_INCR_COUNT = "/incr_count.json";
    const JF_ITEM_ID = 'newdata';
    
    protected $_source = "1941657700";
    

    public function __construct() {
    }

    /**
     * @param $item_id  打点字段名
     * @param $count    累加多少数
     * @param string $weibouid  给那个用户打点
     * @return 接口无异常时的正常返回值
     * @throws Comm_Weibo_Exception_Api
     */
    public  function incr_count ($item_id = self::JF_ITEM_ID, $count, $weibouid)
    {
        try {
            $api = Comm_Weibo_Api_Remind::incr_count();
            $api->source = $this->_source;
            $api->item_id = $item_id;
            $api->value = $count;
            $api->uid = $weibouid;
            $rst = $api->get_rst_detail();
            return $rst;
        } catch (Exception $e) {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    /**
     * 获取的是所有打点字段记录数
     * @return 接口无异常时的正常返回值
     * @throws Comm_Weibo_Exception_Api
     */
    public  function unread_count ()
    {
        try {
            $api = Comm_Weibo_Api_Remind::unread_count();
            $api->source = $this->_source;
            $api->unread_message = 1;
            $api->need_back = 9;
            $rst = $api->get_rst();
            return  $rst;
        } catch (Exception $e) {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    /**
     * @param $item_id  打点字段名
     * @param string $weibouid  给那个用户消点
     * @return 接口无异常时的正常返回值
     * @throws Comm_Weibo_Exception_Api
     */
    public  function clear_count ($item_id = self::JF_ITEM_ID, $weibouid)
    {
        try {
            $api = Comm_Weibo_Api_Remind::clear_count();
            $api->source = $this->_source;
            $api->item_id = $item_id;
            $api->uid = $weibouid;
            $rst = $api->get_rst_detail();
            return  $rst;
        } catch (Exception $e) {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }
}
