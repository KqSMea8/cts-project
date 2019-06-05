<?php
/**
 * feedSDK
 */
define('FEEDSDK_ROOT', T3PPATH . '/feedsdk/v1/FeedSDK');
define('FEEDSDK_CLASSES', FEEDSDK_ROOT . "/Classes");
define('FEEDSDK_CONFIG', FEEDSDK_ROOT . "/Config");
define('FEEDSDK_API', FEEDSDK_ROOT . "/Api");
define('FEEDSDK_TPL', FEEDSDK_ROOT . '/Tpls');

include_once(FEEDSDK_CLASSES . "/Tools.php");
include_once(FEEDSDK_CLASSES . "/FeedPL.php");
include_once(FEEDSDK_API . "/Request.php");
include_once(FEEDSDK_API . "/CommonApi.php");
include_once(FEEDSDK_CLASSES . "/AjaxMapping.php");
//添加配置数组
Comm_Feedsdk1::$AJAX_MAP = $AJAX_MAP;

/**
 * Comm_Feedsdk1 
 * 
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2013-12-05
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Comm_Feedsdk1
{
    //默认feed模板
    CONST FEED_TPL = '/index.php';
    static $AJAX_MAP;

    public function __construct($config)
    {
        //加载配置文件
        $config = empty($config) ? '/Config.php' : $config;
        include_once(FEEDSDK_CONFIG. $config);
    }

    /**
     * getFeedsdk 
     * 
     * 获得feed流
     * @param obj $viewer 预览者
     * @param array $ids 微博id
     * @param int $total 总微博数
     * @param string $pageUrl 翻页url
     * @param array $extra 模板所需额外数据
     * @param string $tpl 模板url
     * @param bool/array $cache 缓存信息('key','name')
     * @return array html代码和删除了的mid数组
     */
    public function getFeedsdk($viewer, $ids, $total, $pageUrl,$extra, $tpl = 'feedpl.php', $cache = false)
    {
        $feedpl = new FeedSDK_FeedPL($viewer);
        //由于对方接口返回微博数据不一样，只传id
        $delMids = $feedpl->initFeedList($ids, 'ids', $total, $pageUrl, $cache);
        $feedpl->prepareData();
        $html = $feedpl->getFeedListHTML($tpl, $extra);
        return array('html' => $html, 'mids' => $delMids);
    }

    /**
     * ajInteractive 
     *
     * ajax交互通用接口
     * @return array (code,msg,data)
     */
    public function ajInteractive()
    {
        global $AJAX_MAP;
        $AJAX_MAP = Comm_Feedsdk1::$AJAX_MAP;
        $type = $_GET['fajtype'];
        unset($_GET['fajtype']);
        if (isset($AJAX_MAP[$type]))
        {
            $method = $AJAX_MAP[$type]['method'];
            if ($method == 'GET')
            {
                $params = $_GET;
            }else
            {
                $params = $_POST;
            }
            $params['apitype'] = $type;
            $obj = new FeedSDK_CommonApi();
            $ret = $obj->request($params, $method);
            if (isset($ret['code']) && $ret['code'] === '100005')
            {
                $ret['code'] = '100002';
            }
            return $ret;
        }else//访问不存在的接口
        {
            $msg['code'] = '100001';
            $msg['msg']  = '系统繁忙';
            $msg['data'] = '';
        }
        return $msg;
    }

    /**
     * ajRelation 
     * 
     * ajax交互通用接口
     * @return array (code,msg,data)
     */
    public function ajRelation()
    {
        include_once T3PPATH . '/feedsdk/v1/RelationSDK/RelationSDK.php';
        $commonApi = 'http://i.profile.service.weibo.com/service/relation';
        $api = new RelationSDK_Request($commonApi, 'POST', true);
        foreach ($_GET as $key => $val) {
                $api->add_query_field($key, $val);
        }
        foreach ($_POST as $key => $val) {
                $api->add_post_field($key, $val);
        }
        $api->timeout = 3000;
        $ret = $api->send();
        if ($ret)
        {
            $response = $api->get_response_content();
            return $response;
        }else//访问不存在的接口
        {
            $msg['code'] = '100001';
            $msg['msg']  = '系统繁忙';
            $msg['data'] = '';
            return $msg;
        }
    }
}
