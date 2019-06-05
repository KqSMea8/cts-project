<?php
/**
 * 订单过期处理30天不填写收货地址为过期订单
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2018/12/05
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Order_Overdue extends Daemon_Abstract
{
    protected $cronName = 'ORDER-OVERDUE';

    public function run()
    {
        try {

            $sql = "select * from ex_product_order where status = ?  and `delivery_status` = ? ";
            $data[] = Model_Const::ORDER_STATUS_PAY;
            $data[] = Model_Const::ORDER_DELIVERY_STATUS_WAIT;
            $ret = Data_Exchange::fetchAll($sql,$data);
            if(!empty($ret)) {
                foreach($ret as $key=>$rowAry) {
                    $retDay = Model_Order::checkCreateTime($rowAry['create_time']);
                    if($retDay > Model_Const::DELIVERY_TIMEOUT_DAY) {
                        //过期订单更改状态
                        Data_Exchange::update('ex_product_order',array('delivery_status' => Model_Const::ORDER_DELIVERY_STATUS_CLOSE,),"order_id = ?",array($rowAry['order_id']));
                    }
                }
            }

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Order_Overdue();  //注意和类名保持一致