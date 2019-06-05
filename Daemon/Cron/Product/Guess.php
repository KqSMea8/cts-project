<?php
/**
 * 计算猜你喜欢商品
 * @author: cts <haoman@staff.weibo.com>
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Product_Guess extends Daemon_Abstract
{
    protected $cronName = 'PRODUCT_GUESS';

    public function run()
    {
        $this->cronName = $this->cronName . time();
        try {

            $productAry = array();
            //获取虚拟商品,查询前七天兑吧商品的兑换量
            $duiba = new Api_Duiba;
            $result = $duiba->queryItem(50,7);
            if ($result['success'] == 1) {
                foreach ($result['data'] as $v) {
                    $productAry[] = array(
                        'product_id' => $v['appItemId'],
                        'pic' => $v['logo'],
                        'title' => $v['title'],
                        'order_count' => $v['exchangeNum'],
                        'url' => $v['url'],
                        'type' => 2,
                    );
                }
            }

            //实物商品，查询当前上架商品前七天的兑换量
            $stime = date('Y-m-d', strtotime('-7 days'))." 00:00:00";
            $etime = date("Y-m-d") . " 23:59:59";
            $sql = "select * from ex_product where status = ? ";
            $data[] = Model_Const::PRODUCT_STATUS_SHOW;
            $ret = Data_Exchange::fetchAll($sql,$data);
            if(!empty($ret)) {
                foreach($ret as $key=>$rowAry) {
                    $picAry =  json_decode($rowAry['pics'],true);
                    $productId = Model_Product::generateLogicProductId($rowAry['create_time'], $rowAry['product_id']);
                    //获取商品的订单数
                    $orderCount = Model_Order::getOrderCountByProductId($productId,$stime,$etime);
                    $productAry[] = array(
                        'product_id' => $productId,
                        'pic' => $picAry['pic1'],
                        'title' => htmlspecialchars_decode($rowAry['name']),
                        'order_count' => $orderCount,
                        'url' => "https://exchange.sc.weibo.com/goodsdetail?product_id=".$productId,
                        'type' => 1,
                    );

                }
            }

            //按兑换量倒叙实物商品
            foreach ($productAry as $key => $row)
            {
                $orderCountSort[$key]  = $row['order_count'];
            }

            array_multisort($orderCountSort, SORT_DESC, $productAry);

            $productInfo = $productAry[0];

            $redis_r = new Lib_Redis(Model_Const::ALIAS_EXCHANGE, Lib_Redis::MODE_WRITE);
            $key_product_guess = 'exchange_product_guess';
            $ret = $redis_r->set($key_product_guess, json_encode($productInfo));

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Product_Guess();  //注意和类名保持一致