<?php

/**
 * Tool_Statistic 
 * 信息系统部日志类
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-2-18
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Tool_Statistic
{
    const PAGE_ITEM = 1;
    const PAGE_MODEL_ITEM = 2;
    const PAGE_ORDER = 3;
    const ORDER_INTERFACE = 4;
    /**
     * getPageName 
     * 获取前一页面名称
     * @return void
     */
    public static function getPageName($type)
    {
        if ($type == 'cur')//当前页
        {
            $pageUrl = $_SERVER['SCRIPT_URI'];
        }else//从给定url获得页面名称
        {
            $pageUrl = $type;
        }

        //删除传参
        if (strpos($pageUrl,'?'))
        {
            $pageUrl = substr($pageUrl,0, strpos($pageUrl,'?'));
        }
        $pageUrl = strtolower($pageUrl);

        if (strpos($pageUrl, 'h5/order/list'))//移动列表
        {
            $pageName = Comm_Config::get('statistic.pageName.h5OrderList');
        }elseif (strpos($pageUrl, 'aj/order/create'))//订单生成接口
        {
            $pageName = Comm_Config::get('statistic.pageName.ajOrderCreate');
        }elseif (strpos($pageUrl, 'h5/seller/product/detail'))//移动商品
        {
            $pageName = Comm_Config::get('statistic.pageName.h5SellerProductDetail');
        }elseif (strpos($pageUrl, 'h5/order/detail'))//移动订单
        {
            $pageName = Comm_Config::get('statistic.pageName.h5OrderDetail');
        }elseif (strpos($pageUrl, 'h5/order/create'))//移动订单生成页
        {
            $pageName = Comm_Config::get('statistic.pageName.h5OrderCreate');
        }elseif (strpos($pageUrl, 'seller/product/detail') || strpos($pageUrl, '/p/100122_') || strpos($pageUrl, 'interface/internal/page/detail'))//PC商品页
        {
            $pageName = Comm_Config::get('statistic.pageName.sellerProductDetail');
        }elseif (strpos($pageUrl, 'order/detail'))//PC订单页
        {
            $pageName = Comm_Config::get('statistic.pageName.orderDetail');
        }elseif (strpos($pageUrl, 'order/confirm'))//PC订单确认页
        {
            $pageName = Comm_Config::get('statistic.pageName.orderConfirm');
        }elseif (strpos($pageUrl, 'order/list') || $pageUrl == 'http://mall.sc.weibo.com/')//PC列表页
        {
            $pageName = Comm_Config::get('statistic.pageName.orderList');
        }elseif(strpos($pageUrl, 'order/create'))//PC订单生成页
        {
            $pageName = Comm_Config::get('statistic.pageName.orderCreate');
        }elseif(strpos($pageUrl, 'h5/aj/tpl/dispatch'))//移动订单生成页
        {
            $type = Comm_Context::param('pageid', false);
            $type = $type ? $type : Comm_Context::param('glocation', '');
            switch ($type)
            {
                case 'list':$pageName = Comm_Config::get('statistic.pageName.h5OrderDetail');break;
                case 'receive':$pageName = '收礼页';break;
                case 'detail':$pageName = Comm_Config::get('statistic.pageName.h5OrderDetail');break;
                case 'coupon':$pageName = '优惠券';break;
                case 'addaddr':$pageName = '添加收货地址';break;
                case 'seladdr':$pageName = '选择收货地址';break;
                case 'invoice':$pageName = '发票';break;
                case 'shipinfo':$pageName = '个人信息';break;
                case 'comment':$pageName = '发票';break;
                default:$pageName = Comm_Config::get('statistic.pageName.h5OrderCreate');break;
            }
        }
        return array('name'=>$pageName,'url'=>$pageUrl);
    }

    public static function getPrePageName($type)
    {
        $pageUrl = $_SERVER['HTTP_REFERER'];

        //删除传参
        if (strpos($pageUrl,'?'))
        {
            $pageUrl = substr($pageUrl,0, strpos($pageUrl,'?'));
        }
        $pageUrl = strtolower($pageUrl);

        if (strpos($pageUrl, 'h5/order/list'))//移动列表
        {
            $pageName = Comm_Config::get('statistic.pageName.h5OrderList');
        }elseif (strpos($pageUrl, 'aj/order/create'))//订单生成接口
        {
            $pageName = Comm_Config::get('statistic.pageName.ajOrderCreate');
        }elseif (strpos($pageUrl, 'h5/seller/product/detail'))//移动商品
        {
            $pageName = Comm_Config::get('statistic.pageName.h5SellerProductDetail');
        }elseif (strpos($pageUrl, 'h5/order/detail'))//移动订单
        {
            $pageName = Comm_Config::get('statistic.pageName.h5OrderDetail');
        }elseif (strpos($pageUrl, 'h5/order/create'))//移动订单生成页
        {
            $pageName = Comm_Config::get('statistic.pageName.h5OrderCreate');
        }elseif (strpos($pageUrl, 'seller/product/detail') || strpos($pageUrl, '/p/100122_') || strpos($pageUrl, 'order/order') || strpos($pageUrl, 'interface/internal/page/detail'))//PC商品页
        {
            $pageName = Comm_Config::get('statistic.pageName.sellerProductDetail');
        }elseif (strpos($pageUrl, 'order/detail'))//PC订单页
        {
            $pageName = Comm_Config::get('statistic.pageName.orderDetail');
        }elseif (strpos($pageUrl, 'order/confirm'))//PC订单确认页
        {
            $pageName = Comm_Config::get('statistic.pageName.orderConfirm');
        }elseif (strpos($pageUrl, 'order/list') || $pageUrl == 'http://mall.sc.weibo.com/')//PC列表页
        {
            $pageName = Comm_Config::get('statistic.pageName.orderList');
        }elseif(strpos($pageUrl, 'mall.sc.weibo.com/order/create') !== false)//PC订单生成页
        {
            $pageName = Comm_Config::get('statistic.pageName.orderCreate');
        }elseif (strpos($pageUrl, 'sinaurl'))
        {
            $pageName = Comm_Config::get('statistic.pageName.h5SellerProductDetail');
        }elseif(strpos($pageUrl, 'h5/aj/tpl/dispatch'))//移动分发页
        {
            $type = Comm_Context::param('pageid', false);
            $type = $type ? $type : Comm_Context::param('glocation', '');
            switch ($type)
            {
                case 'list':$pageName = Comm_Config::get('statistic.pageName.h5OrderDetail');break;
                case 'receive':$pageName = '收礼页';break;
                case 'detail':$pageName = Comm_Config::get('statistic.pageName.h5OrderDetail');break;
                case 'coupon':$pageName = '优惠券';break;
                case 'addaddr':$pageName = '添加收货地址';break;
                case 'seladdr':$pageName = '选择收货地址';break;
                case 'invoice':$pageName = '发票';break;
                case 'shipinfo':$pageName = '个人信息';break;
                case 'comment':$pageName = '发票';break;
                default:$pageName = Comm_Config::get('statistic.pageName.h5OrderCreate');break;
            }
        }
        return array('name'=>$pageName,'url'=>$pageUrl);
    }

    /**
     * addStatisticLog 
     * 添加统计日志
     * @param mixed $curPage //现在已作废
     * @param mixed $uid
     * @param mixed $orderId 
     * @param mixed $iid 
     * @return void
     */
    public static function addStatisticLog($params)
    {
        $firstPageInfo = $params['firstPageInfo'];
        $uid = $params['uid'];
        $orderId = $params['orderId'];
        $amount = $params['amount'];
        $count = $params['count'];
        $cid = $params['cid'];
        $firstPageInfo = explode('-', $firstPageInfo);
        $fp_timestamp = $firstPageInfo[0];
        $iid = $firstPageInfo[1];
        $item_type = $firstPageInfo[2];
        $virtual_order_id = sprintf("%05d", $iid) . $uid . $firstPageInfo[3];
        $firmId = $firstPageInfo[4];
        $first_page = Tool_Statistic::getFirstPage($firstPageInfo[5], $iid, $orderId);
        $prePageInfo = Tool_Statistic::getPrePageName('pre');
        $curPageInfo = Tool_Statistic::getPageName('cur');
        $statistic = urldecode($firstPageInfo[6]);
        $from = $firstPageInfo[7];
        $adid = $firstPageInfo[8];

        $ip = Comm_Context::get_client_ip();

        $msg = "current_page:{$curPageInfo['name']};{$curPageInfo['url']}|first_page:{$first_page['name']};{$first_page['url']}|fp_timestamp:{$fp_timestamp}|pre_page:{$prePageInfo['name']};{$prePageInfo['url']}|uid:$uid|item_id:$iid|firm_id:$firmId|item_type:$item_type|timestamp:" . strtotime(now) . "|client_msg:{$statistic}|order_id:$orderId|amount:$amount|virtual_order_id:{$virtual_order_id}|ip:$ip|from:$from|uve_adid:$adid|count:{$count}|cid:{$cid}|session_id:" . $_COOKIE['Apache'] . '|cookie_id:' . $_COOKIE['SINAGLOBAL'];
        //$msg = explode('|', $msg);
        //$msg = implode("\n",$msg);
        //Tool_Log::statistic("\n".$msg . "\n");
        Tool_Log::statistic($msg . "\n");
        return array('c' => $from,'uve_adid' => $adid, 'uid' => $uid, 'iid' => $iid, 'firm_id' => $firmId, 'item_type' => $item_type, 'client_msg' => $statistic, 'oid' => $orderId, 'amount' => $amount, 'count' => $count, 'cid' => $cid);
    }

    /**
     * getFrom 
     * 获取来源参数
     * @param mixed $params 
     * @static
     * @return void
     */
    public static function getFrom($params){
        $firstPageInfo = $params['firstPageInfo'];
        $firstPageInfo = explode('-', $firstPageInfo);
        $statistic = urldecode($firstPageInfo[6]);
        $from = $firstPageInfo[7];
        $adid = $firstPageInfo[8];
        return array('from' => $from, 'uve_adid' => $adid, 'client_msg' => $statistic);
    }

    /**
     * setFirstPageData 
     * 获得用户首次进入订单流程信息
     * @param mixed $iid 
     * @param mixed $itemType 
     * @param mixed $uid 
     * @static
     * @return void
     */
    public static function setFirstPageData($iid, $itemType, $uid, $firmId, $page)
    {
        $virtual_order_id = rand(10000, 99999);
        if ($page == 'item')
        {
            $page = self::PAGE_ITEM;
        }else if ($page == 'mobile_item')
        {
            $page = self::PAGE_MODEL_ITEM;
        }else if ($page == 'order')
        {
            $page = self::PAGE_ORDER;
        }else if($page == 'interface'){
            $page = self::ORDER_INTERFACE;
        }

        $itemType = $itemType == Do_Item_Info::MATERIAL_TYPE ? 
            Comm_Config::get('statistic.item_type.material') : Comm_Config::get('statistic.item_type.virtual');

        //记录客户端参数
        if (Comm_Context::param('emobileanalysisext', false))
        {
            $statistic = urlencode(Comm_Context::param('emobileanalysisext', ''));
        }else{
            $statistic = '';
        }

        $adid = Comm_Context::param('uve_adid', '');//商品趋势
        $from = Comm_Context::param('c', '');//入口信息

        //PC参数c
        if (!$from){
            $url = $_SERVER['HTTP_REFERER'];
            if ($url){
                $pos = strpos($url, '?');
                $params = substr($url, $pos +1, strlen($url) - $pos);
                $params = explode('&', $params);
                foreach($params as $val){
                    $param = explode('=', $val);
                    if ($param[0] == 'c'){
                        $from = $param[1];
                    }
                }
            }
        }
        $str = strtotime(now) . '-' . $iid . '-' . $itemType . '-' . $virtual_order_id . '-' . $firmId . '-' . $page . '-' . $statistic . '-' . $from  . '-' . $adid;
        return htmlentities($str, ENT_QUOTES, 'utf-8');
    }

    /**
     * getFirstPage 
     * 首页信息
     * @param mixed $type 
     * @param mixed $iid 
     * @param mixed $oid 
     * @static
     * @return void
     */
    public static function getFirstPage($type, $iid, $oid)
    {
        switch($type)
        {
            case self::PAGE_ITEM:
                $pageUrl = Tool_Misc::getPageUrl($iid);
                return array('name' => Comm_Config::get('statistic.pageName.sellerProductDetail'), 'url' => $pageUrl);
            case self::PAGE_MODEL_ITEM:
                return array('name' => Comm_Config::get('statistic.pageName.h5SellerProductDetail'), 'url' => Comm_Config::get('domain.ordersc') . '/seller/product/detail?iid=' . $iid);
            case self::PAGE_ORDER:
                return array('name' => Comm_Config::get('statistic.pageName.h5OrderDetail'), 'url' => Comm_Config::get('domain.ordersc') . '/h5/order/detail?orderid=' . $oid);
            case self::ORDER_INTERFACE:
                return array('name' => Comm_Config::get('statistic.pageName.h5OrderDetail'), 'url' => Comm_Config::get('domain.ordersc') . '/h5/order/detail?orderid=' . $oid);
        }
    }

    /**
     * addEnvelopeLog 
     * 微博红包log
     * @param mixed $data 
     * @static
     * @return void
     */
    public static function addEnvelopeLog($data){
        $log = array();
        $log[] = date('Y-m-d H:i:s');//打码时间(必填)
        $log[] = $data['uid'];//用户uid（必填）
        $log[] = $data['action'];//行为码（必填）
        $log[] = $data['target_id'];//行为目标id
        $log[] = $data['uicode'];//本级ui编码（必填）
        $log[] = $data['main_id'];//本级ui页面的标记主id
        $log[] = $data['previous_id'];//上一级主id
        $log[] = $data['previous_uicode'];//上一级ui编码（必填）
        $log[] = $data['cardid'];//本级page页的itemid
        $log[] = $data['lcardid'];//上级page页的itemid
        $log[] = $data['function_code'];//功能编码
        $log[] = $data['from_val'];//from值
        $log[] = $data['wm'];//wm值
        $log[] = $data['oldwm'];//old wm值
        $log[] = Comm_Context::get_client_ip();//客户端ip
        $log[] = 4;//日志版本（必填）
        $log[] = $data['extend'];//扩展字段（必填）

        $statistic = implode('`', $log);
        Tool_Log::re($statistic);
    }
}
