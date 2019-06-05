<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2019/1/7
 * Time: 3:00 PM
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Tool_Dxj_Pay2validate extends Daemon_Abstract
{
    use Lib_Traits_Redis;

    protected $cronName = 'DXJ_PAY2CHECK';

    public function run()
    {
        $redis = static::connection(Model_Const::REDIS_ALIAS_EXCHANGE_SECURITY_PUSH);

        try {
            $sql = "select * from ex_money_order where uid = 6672189008 and order_id = 1088560 limit 1";

            $ret = Data_Exchange::fetchAll($sql);
            if(!empty($ret)) {
                foreach($ret as $key=>$row) {
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
new Daemon_Cron_Tool_Dxj_Pay2validate();
