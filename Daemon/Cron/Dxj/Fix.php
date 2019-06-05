<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2018/12/19
 * Time: 12:15 PM
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Dxj_Fix extends Daemon_Abstract
{
    protected $cronName = 'DXJ_FIX';

    public function run()
    {
        try {
            $time = date('Y-m-d H:i:00', time() - 300);
            $sql = "select * from ex_money_order where status=? and create_time<? limit 1000";
            $data = array(Model_Const::MONEY_ORDER_STATUS_CREATE, $time);
            $ret = Data_Exchange::fetchAll($sql, $data);
            if(!empty($ret)) {
                foreach($ret as $key=>$row) {
                   $queryResult = Model_JF::consumeQueryDxj($row['uid'], $row['order_id']);
                   $this->info("query consume result:{$row['uid']}|{$row['order_id']}|" . json_encode($queryResult));
                   if($queryResult['code'] == '300005'){//未支付成功
                       $sqlUpdate = 'update ex_money_order set status=? where order_id=?';
                       Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_CLOSE, $row['order_id']));
                       Model_Cache::decrTodayCount($row['uid'], $row['money']);
                       Model_Cache::decrTodayCashPoolingCount($row['money']);
                   }else if($queryResult['code'] == '100000'){//已经支付成功
                       if(($queryResult['data']['buyer_uid'] != $row['uid']) ||
                           ($queryResult['data']['jf'] != $row['score'])){//查询信息对比
                           $this->warning('consume msg not match:' . json_encode($row) . '|' . json_encode($queryResult['data']));
                           continue;
                       }else {
                           $sqlUpdate = 'update ex_money_order set status=?,consume_id=?,consume_time=? where order_id=?';
                           //设置为支付成功
                           /*$updateRet = Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_PAY, $queryResult['data']['consume_id'], $queryResult['data']['consume_time'], $row['order_id']));
                           if (!empty($updateRet)) {
                               //更新缓存
                               Model_Cache::incrTodayCount($row['uid'], $row['money']);
                               Model_Cache::incrTodayCashPoolingCount($row['money']);
                           }*/
                           Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_PAY, $queryResult['data']['consume_id'], $queryResult['data']['consume_time'], $row['order_id']));

                       }

                   }else{
                       $this->warning("未处理的code：{$queryResult['code']}");
                   }
                }
            }

        } catch (Exception $e) {
            $this->warning('Unknown Exception:' . $e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Dxj_Fix();