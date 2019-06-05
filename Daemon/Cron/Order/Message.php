<?php
/**
 * 检查未填写收货地址的订单发私信
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2018/09/05
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Order_Message extends Daemon_Abstract
{
    protected $cronName = 'ORDER-MESSAGE';

    public function run()
    {
        try {
            $uidAry = array();
            $date = date("Y-m-d");

            $redis = new Lib_Redis(Model_Const::REDIS_ALIAS_EXCHANGE, Lib_Redis::MODE_WRITE);
            $redisKey = 'order_no_delivery_'.date("Ymd");
            $retRedisStr = $redis->get($redisKey);
            $uidRedisAry = json_decode($retRedisStr, true);

            $sql = "select * from ex_product_order where status = ?  and `delivery_status` = ? ";
            $data[] = Model_Const::ORDER_STATUS_PAY;
            $data[] = Model_Const::ORDER_DELIVERY_STATUS_WAIT;
            $ret = Data_Exchange::fetchAll($sql,$data);
            if(!empty($ret)) {
                foreach($ret as $key=>$rowAry) {
                    $diffDays = $this->diffBetweenTwoDays($date,date("Y-m-d",strtotime($rowAry['create_time'])));
                    if($diffDays == 7) {
                        $uidAry['message_7'][$rowAry['uid']] = array(
                            'uid' => $rowAry['uid'],
                        );
                    }
                    if($diffDays == 29) {
                        $uidAry['message_29'][$rowAry['uid']] = array(
                            'uid' => $rowAry['uid'],
                        );
                    }
                }

                foreach($uidAry['message_7'] as $uid=>$row) {
                    if(!in_array($row['uid'],$uidRedisAry['message_7'])) {
                        $msg_map = array(
                            'uid' => $row['uid'],
                            'time' => date('Y-m-d H:i:s'),
                        );
                        Model_Notify::remindAddress($msg_map);
                        $uidRedisAry['message_7'][] = $row['uid'];
                    }
                }


                foreach($uidAry['message_29'] as $uid=>$row) {
                    if(!in_array($row['uid'],$uidRedisAry['message_29'])) {
                        $msg_map = array(
                            'uid' => $row['uid'],
                            'time' => date('Y-m-d H:i:s'),
                        );
                        Model_Notify::overdueAddress($msg_map);
                        $uidRedisAry['message_29'][] = $row['uid'];
                    }
                }
            }

            //记录发送成功的用户
            $redis = new Lib_Redis(Model_Const::REDIS_ALIAS_EXCHANGE, Lib_Redis::MODE_WRITE);
            $redis->set($redisKey, json_encode($uidRedisAry));

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }

    private function diffBetweenTwoDays ($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }
}
new Daemon_Cron_Order_Message();  //注意和类名保持一致