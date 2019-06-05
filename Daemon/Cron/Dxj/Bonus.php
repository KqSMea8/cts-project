<?php
/**
 * 商户发红包接口
 * User: cts
 * Date: 2019/1/7
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Dxj_Bonus extends Daemon_Abstract
{
    protected $cronName = 'DXJ_BONUS';

    public function run()
    {
        try {
            //$time = date('Y-m-d H:i:00', time() - 300);
            //$sql = "select * from ex_money_order where status=? and create_time<? limit 1000";
            $sql = "select * from ex_money_order where status=? limit 1000";
            $data = array(Model_Const::MONEY_ORDER_STATUS_ACCEPT);
            $ret = Data_Exchange::fetchAll($sql, $data);
            if(!empty($ret)) {
                foreach($ret as $key=>$row) {
                    //发红包
                    $amount = $row['money'] * 100;
                    $redRes = Model_Bonus::send($row['order_id'], Model_JF::BONUS_TPL_ID, $amount, $row['uid']);//todo tplID没定
                    if($redRes) {
                        if (!isset($redRes['code'])) {
                            $redRes['code'] = $redRes['error_code'];
                        }
                        if (!isset($redRes['msg'])) {
                            $redRes['msg'] = $redRes['error'];
                        }
                        echo "{$redRes['code']},{$redRes['msg']}\n";
                        if ($redRes['code'] == '100000') {
                            $this->warning("发红包成功：".json_encode($redRes)."  id : {$row['order_id']}");
                            //红包发送成功
                            $sqlUpdate = "update ex_money_order set status = ? ,bonus_time = ? , bonus_id = ? where order_id = ?";
                            Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_BONUS, $redRes['data']['process_time'], $redRes['data']['bonus_id'], $row['order_id']));

                            //发送成功用户写队列发私信
                            $msg_map = array(
                                'uid' => $row['uid'],
                                'time' => date('Y-m-d H:i:s'),
                                'price' =>$row['money'],
                            );
                            Model_Notify::bonusSuccess($msg_map);

                        } else {
                            //发送失败
                            $sqlUpdate = "update ex_money_order set status = ? , bonus_fail_msg = ? where order_id = ?";
                            Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_FAIL, $redRes['msg'], $row['order_id']));
                            $this->warning("发红包未处理的code：{$redRes['code']}  id : {$row['order_id']}");
                        }
                    }else {//无返回也算失败
                        $sqlUpdate = "update ex_money_order set status = ? , bonus_fail_msg = ? where order_id = ?";
                        Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_FAIL, '', $row['order_id']));
                    }
                }
            }

        } catch (Exception $e) {
            $this->warning('Unknown Exception:' . $e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Dxj_Bonus();