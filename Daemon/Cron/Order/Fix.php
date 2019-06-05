<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2018/12/25
 * Time: 5:32 PM
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Order_Fix extends Daemon_Abstract
{
    protected $cronName = 'ORDER-FIX';

    public function run()
    {
        try {
            $start = '2018-12-27 16:00:00';
            $time = date('Y-m-d H:i:00', time() - 300);
            for($i=0;$i<10;$i++) {
                $sql  = 'select uid,order_id,product_id,score,create_time from ' . Data_Exchange::TABLE_PRODUCT_ORDER . ' where create_time>=? and  create_time<=? and status=? limit 100';
                echo "{$start},{$time}\n";
                $data = array($start, $time, Data_Order::STATUS_CREATED);
                $ret  = Data_Exchange::fetchAll($sql, $data);
                if (!empty($ret)) {
                    foreach ($ret as $key => $row) {
                        $queryResult = Model_JF::consumeQueryDx($row['uid'], $row['order_id']);
                        if ($queryResult['code'] == '300005') {//未支付成功
                            $sqlUpdate = 'update ' . Data_Exchange::TABLE_PRODUCT_ORDER . ' set status=? where order_id=?';
                            Data_Exchange::exec($sqlUpdate, array(Data_Order::STATUS_CLOSE, $row['order_id']));
                            echo "unpay:{$row['order_id']}\n";
                        } else if ($queryResult['code'] == '100000') {//已经支付成功
                            if (($queryResult['data']['buyer_uid'] != $row['uid']) ||
                                ($queryResult['data']['jf'] != $row['score'])) {//查询信息对比
                                $this->warning('consume msg not match:' . json_encode($row) . '|' . json_encode($queryResult['data']));
                                continue;
                            }
                            $productId = Model_Product::analysisLogicProductId($row['product_id']);
                            Data_Order::decrementStockAndUpdateOrderStatus($row['order_id'], $productId, $queryResult['data']['consume_id'], $queryResult['data']['consume_time']);
                            echo "pay:{$row['order_id']}\n";
                        } else {
                            $this->warning("未处理的code：{$queryResult['code']}");
                        }
                        $start = $row['create_time'];
                    }
                }else{
                    exit("没有需要处理的请求");
                }

            }

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Order_Fix();  //注意和类名保持一致