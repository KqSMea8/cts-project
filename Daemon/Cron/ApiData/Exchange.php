<?php
/**
 * 兑换api提供的统计数据
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2019/01/29
 */
ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_ApiData_Exchange extends Daemon_Abstract
{
    protected $cronName = 'APIDATA_EXCHANGRE';
    protected $product_cache;

    public function run()
    {
        try {

            $start = date('Y-m-d', strtotime('-1 days'))." 00:00:00";
            $end = date('Y-m-d', strtotime('-1 days'))." 23:59:59";
            $date = date('Ymd', strtotime('-1 days'));

            $supplyPrice = 0;

            $orderId_index = 0;
            while (1) {
                $sql = "select `order_id`,`product_id`  from `ex_product_order` where `order_id` > {$orderId_index} and `status` = " . Model_Const::ORDER_STATUS_PAY . " and `create_time` >= '{$start}' and `create_time` <= '{$end}' limit 1000 ";
                $ret = Data_Exchange::fetchAll($sql);
                if (empty($ret)) {
                    break;
                }
                foreach ($ret as $key => $rowAry) {
                    $orderId_index = $rowAry['order_id'];
                    //查询商品信息计算供应价
                    $productInfo = $this->productCache($rowAry['product_id']);
                    if ($productInfo) {
                        $supplyPrice += $productInfo['supply_price'] / 100;
                    }
                }

            }

            $dataAry['supply_price'] = $supplyPrice;

            $redis = new Lib_Redis(Model_Const::REDIS_ALIAS_EXCHANGE, Lib_Redis::MODE_WRITE);
            $key = 'api_data_exchange_'.$date;
            $redis->set($key, json_encode($dataAry));

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }



    private function productCache($product_id)
    {
        if ($this->product_cache[$product_id]) {
            return $this->product_cache[$product_id];
        }

        $product = $this->getProductByPid($product_id);
        $this->product_cache[$product_id] = $product;
        return $product;
    }

    private function getProductByPid($product_id)
    {
        $productId = Model_Product::analysisLogicProductId($product_id);
        $productInfo = Data_Product::getProductInfo($productId);
        return $productInfo;
    }
}
new Daemon_Cron_ApiData_Exchange();  //注意和类名保持一致