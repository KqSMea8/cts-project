<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2019/1/7
 * Time: 3:00 PM
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Dxj_Pay2validate extends Daemon_Abstract
{
    use Lib_Traits_Redis;

    protected $cronName = 'DXJ_PAY2CHECK';

    public function run()
    {
        $redis = static::connection(Model_Const::REDIS_ALIAS_EXCHANGE_SECURITY_PUSH);

        try {
            $time = date('Y-m-d H:i:00', time());
            $sql = "select * from ex_money_order where status=? and create_time<? limit 1000";
            $data = array(Model_Const::MONEY_ORDER_STATUS_PAY, $time);
            $ret = Data_Exchange::fetchAll($sql, $data);
            if(!empty($ret)) {
                foreach($ret as $key=>$row) {
                    $sqlUpdate = 'update ex_money_order set status=? where order_id=?';
                    Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_VALIDATE, $row['order_id']));

                    $data = [
                        'order_id' => $row['order_id'],
                        'uid' => $row['uid'],
                        'money' => $row['money'],
                        'create_time' => strtotime($row['create_time']),
                    ];
                    $redis->lpush('queue_withdrawal_jf_safe_check', json_encode($data));
                }
            }

        } catch (Exception $e) {
            $this->warning('Unknown Exception:' . $e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Dxj_Pay2validate();
