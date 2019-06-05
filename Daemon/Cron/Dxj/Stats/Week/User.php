<?php
//T+1
ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/stdafx.php'; //每个PHP脚本都要引入这个文件

/**
 * 日报,旧用户
 */
class Daemon_Cron_Dxj_Stats_Week_User extends Daemon_Abstract
{
    protected $start;
    protected $end;
    protected $date;
    protected $where_ctime;//创建时间
    protected $where_paytime;//支付时间
    const DBNAME = Model_Const::ALIAS_EXCHANGE;

    public function run()
    {
        echo 'start time' . date('Y-m-d H:i:s') . PHP_EOL;

        $this->start = date('Y-m-d', strtotime('-7 days'));
        $this->end = date('Y-m-d', strtotime('-1 days'));
        $this->where_paytime = "`status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and create_time>='{$this->start} 00:00:00' and create_time<='{$this->end} 23:59:59'";

        $redis_w = new Lib_Redis(self::DBNAME, Lib_Redis::MODE_WRITE);
        $db = new Lib_Mysql(self::DBNAME);
        $key_t = 'exchange_bonus_stat_str_new_users_week'.date('Ymd', strtotime('-1 days'));//当天新用户总数

        $sql = "select uid from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `create_time`<'{$this->start} 00:00:00'  group by uid;"; //全量的用户集合
        $ret = $db->fetchAll($sql);
        foreach ($ret as $index => $data) {
            $total_user[$data['uid']] = 1;//全部旧用户
        }



        $sqlNew = "select * from `ex_money_order` where {$this->where_paytime} ";//当天的用户集合
        $new_paid_count = 0;

        $ret = $db->fetchAll($sqlNew);

        $new_paid_users = array();
        $new_paid_money = 0;

        foreach ($ret as $index => $data) {
            if(isset ($total_user[$data['uid']]) ){
                //旧用户里存在
            } else {
                $new_paid_users[$data['uid']] = 1;
                $new_paid_money += $data['score'];
            }
            $new_paid_count++;
        }


        $set_arr = array(
            'pay_count' => $new_paid_count,
            'pay_users' => count($new_paid_users),
            'pay_score' => $new_paid_money
        );

        print_r($set_arr);

        $r = $redis_w->set($key_t, json_encode($set_arr));

        var_dump($r);

        return;
    }

}
new Daemon_Cron_Dxj_Stats_Week_User();
