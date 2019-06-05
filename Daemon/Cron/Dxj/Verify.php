<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2019/1/21
 * Time: 10:56 AM
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Dxj_Verify extends Daemon_Abstract
{
    protected $cronName = 'DXJ_EXTRA';

    public function run()
    {
        try {
            $orderId = 0;
            while(true) {
                $sql  = 'select order_id,uid,score,poundage from ex_money_order where order_id>? and status=? order by order_id asc limit 1000';
                $data = array($orderId, Model_Const::MONEY_ORDER_STATUS_BONUS);
                $ret  = Data_Exchange::fetchAll($sql, $data);

                if(empty($ret)){
                    break;
                }

                foreach ($ret as $key => $row) {
                    $verifyScore = $row['score'] - $row['poundage'];
                    $verifyRet   = Model_JF::verifyApplyDxj($row['uid'], $row['order_id'], $verifyScore);
                    if ($verifyRet['code'] == '100000') {
                        $this->info("score verify success:{$row['order_id']}|{$verifyScore}|{$verifyRet['data']['verify_id']}");
                        $sqlUpdate = 'update ex_money_order set status=? where order_id=?';
                        Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_VERIFY, $row['order_id']));
                    } else if ($verifyRet['code'] == '500003') {
                        $this->info("score verify 500003:{$row['order_id']}|{$verifyScore}");
                        $sqlUpdate = 'update ex_money_order set status=? where order_id=?';
                        Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_VERIFY, $row['order_id']));
                    } else {
                        $this->warning('fail to verify ' . $row['order_id'] . '|' . json_encode($verifyRet));
                    }
                }

                $orderId = $row['order_id'];
            }

        } catch (Exception $e) {
            $this->warning('Unknown Exception:' . $e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Dxj_Verify();