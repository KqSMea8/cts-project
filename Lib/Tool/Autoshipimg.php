<?php
/**
 * Created by JetBrains PhpStorm.
 * User: shiliang5
 * Date: 14-8-15
 * Time: 下午7:32
 * To change this template use File | Settings | File Templates.
 */
class Tool_Autoshipimg {
    public static function get_autoship_img ($order_info, $waybill) {
        Tool_log::warning(" get autoship img enter ".var_export($order_info, true)." waybill: ".$waybill);
        if (!$order_info || !$waybill) {
            return $waybill;
        }
        $sellcentership    = new Model_Order_Info();
        $order_info_buyer  = $sellcentership->get_order_info($order_info['oid'], $order_info['uid']);
        //是否是自动发货
        $oid = $order_info_buyer['oid'];
        $uid = $order_info_buyer['uid'];
        $firm_id = $order_info_buyer['firm_id'];
        $model_code_codeshare = new Model_Code_Codeshare();
        $codeInfo = $model_code_codeshare->get_code_by_code_oid($waybill, $oid);
        if ($codeInfo) {
            $url = "http://mall.sc.weibo.com/h5/codemanage/detail?".
                "code={$waybill}&oid={$oid}&uid={$uid}&firm_id={$firm_id}";
            $model_order_detail = new Model_Order_Detail();
            $commodityInfos = $model_order_detail->orderCommodityInfos($firm_id,$oid);
            $commodityInfos = $commodityInfos[0];
            $iid = $commodityInfos['iid'];
            $model_item_info = new Model_Item_Info();
            $itemInfo = $model_item_info->get_item_info($iid, $firm_id);

            if ($itemInfo['code_verify'] == 0) {
                $img = $waybill;
            }
            else {
                $url = urlencode($url);
                $url = Tool_Shorturl::shorten($url) ;
                $img = $url;
            }
        }
        else {
            $img = $waybill;
        }
        Tool_log::warning(" get autoship img end ".$img);
        return $img;
    }
}