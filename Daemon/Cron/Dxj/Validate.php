<?php
/**
 * 模拟安全审核
 * User: zhongfeng
 * Date: 2019/1/7
 * Time: 2:57 PM
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Dxj_Validate extends Daemon_Abstract
{
    use Lib_Traits_Redis;

    protected $cronName = 'DXJ_EXTRA';

    public function run()
    {
        $redis = static::connection(Model_Const::REDIS_ALIAS_EXCHANGE_SECURITY_POP);

        try {
            /*
            $time = date('Y-m-d H:i:00', time() - 300);
            $sql = "select * from ex_money_order where status=? and create_time<? limit 1000";
            $data = array(Model_Const::MONEY_ORDER_STATUS_VALIDATE, $time);
            $ret = Data_Exchange::fetchAll($sql, $data);
            */
            Lib_Log::debug("Validate script start");
            while($ret = $redis->lpop('queue_withdrawal_jf_safe_check_back')) {
                Lib_Log::debug("Validate Queue data: {$ret}");
                $data = json_decode($ret, true);

                if (isset($data['permit'])) {
                    switch($data['permit']) {
                    case 0:
                        $sqlUpdate = 'update ex_money_order set status=? where order_id=? and status=?';
                        Data_Exchange::exec(
                            $sqlUpdate,
                            array(
                                Model_Const::MONEY_ORDER_STATUS_REJECT,
                                $data['order_id'],
                                Model_Const::MONEY_ORDER_STATUS_VALIDATE
                            )
                        );
                    case 1:
                        $sqlUpdate = 'update ex_money_order set status=? where order_id=? and status=?';
                        Data_Exchange::exec(
                            $sqlUpdate,
                            array(
                                Model_Const::MONEY_ORDER_STATUS_ACCEPT,
                                $data['order_id'],
                                Model_Const::MONEY_ORDER_STATUS_VALIDATE
                            )
                        );
                        break;
                    }
                }
            }
            /*
            if(!empty($ret)) {

                foreach($ret as $key=>$row) {
                    $sqlUpdate = 'update ex_money_order set status=? where order_id=?';
                    Data_Exchange::exec($sqlUpdate, array(Model_Const::MONEY_ORDER_STATUS_ACCEPT, $row['order_id']));

                }

            }
            */
        } catch (Exception $e) {
            $this->warning('Unknown Exception:' . $e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Dxj_Validate();