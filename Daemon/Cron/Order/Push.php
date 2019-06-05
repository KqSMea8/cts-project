<?php
/**
 * 给进销存系统推用户维护收货地址的订单数据
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2019/05/09
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Order_Push extends Daemon_Abstract
{
    protected $cronName = 'ORDER-PUSH';

    public function run()
    {
        try {

            $sql = "select * from ex_product_order where `status` = ?  and `delivery_status` = ? and `push_status` = ? ";
            $data[] = Model_Const::ORDER_STATUS_PAY;
            $data[] = Model_Const::ORDER_DELIVERY_STATUS_ADDRESS;
            $data[] = Model_Const::ORDER_PUSH_STATUS_WAIT;
            $ret = Data_Exchange::fetchAll($sql,$data);
            if(!empty($ret)) {
                foreach($ret as $key=>$rowAry) {
                    $obj = new Api_Settle();
                    $result = $obj->orderInput($rowAry['order_id'],$rowAry['create_time'],$rowAry['uid'],$rowAry['score'],$rowAry['buy_number'],$rowAry['goods_id'],$rowAry['delivery_tel'],$rowAry['delivery_address'],$rowAry['delivery_name']);
                    if($result['code'] == 100000) {
                        //推送成功修改状态
                        $sqlUpdate = 'update ' . Data_Exchange::TABLE_PRODUCT_ORDER . ' set push_status=? where order_id=?';
                        Data_Exchange::exec($sqlUpdate, array(Model_Const::ORDER_PUSH_STATUS_SUCCESS, $rowAry['order_id']));
                    }else {
                        continue;
                    }
                }
            }

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Order_Push();  //注意和类名保持一致