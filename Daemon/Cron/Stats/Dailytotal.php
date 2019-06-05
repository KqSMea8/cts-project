<?php
//T+1
ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/stdafx.php'; //每个PHP脚本都要引入这个文件

/**
 * 日报
 */
class Daemon_Cron_Stats_Dailytotal extends Daemon_Abstract
{
    protected $start;
    protected $end;
    protected $date;
    protected $where_ctime;//创建时间
    const DBNAME = Model_Const::ALIAS_EXCHANGE;

    public function run()
    {
        $this->start = '2018-11-30 20:00:00';
        $this->end = date('Y-m-d', strtotime('-1 days'))." 23:59:59";
        $this->date = date('Ymd', strtotime('-1 days'));

        $out = array();

        $order = $this->historyOrder();
        $out['order_count'] = $order['order_count'];
        $out['order_users'] = $order['order_users'];
        $out['order_score'] = $order['order_score'];
        $out['order_arpu'] = $order['order_arpu'];
        $out['order_asp'] = $order['order_asp'];
        $out['order_average'] = $order['order_average'];


        $redis_r = new Lib_Redis(self::DBNAME, Lib_Redis::MODE_WRITE);
        $key_t = 'exchange_stat_str_history_data';
        $ret = $redis_r->set($key_t, json_encode($out));
        var_dump($out);
        return;
    }


    private function historyOrder(){
        $db = new Lib_Mysql(self::DBNAME);
        $userCount = 0;
        $orderCount = 0;
        $score = 0;

        //兑换用户数
        $sql1 = "select count(DISTINCT(uid)) as total from `ex_product_order` where `product_id` not in('1543568453100017','11543549070100010','11543547500100004','11543489045100000') and `order_id` not in ('100115','100116','101086') and `status` = ".Model_Const::ORDER_STATUS_PAY." and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret1 = $db->fetchOne($sql1);
        if($ret1['total']) {
            $userCount = $ret1['total'];
        }

        //总订单数
        $sql2 = "select count(*) as total from `ex_product_order` where `product_id` not in('1543568453100017','11543549070100010','11543547500100004','11543489045100000') and `order_id` not in ('100115','100116','101086') and `status` = ".Model_Const::ORDER_STATUS_PAY." and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret2 = $db->fetchOne($sql2);
        if($ret2['total']) {
            $orderCount = $ret2['total'];
        }

        //兑换总积分
        $sql7 = "select sum(`score`) as total from `ex_product_order` where `product_id` not in('1543568453100017','11543549070100010','11543547500100004','11543489045100000') and `order_id` not in ('100115','100116','101086') and `status` = ".Model_Const::ORDER_STATUS_PAY." and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret7 = $db->fetchOne($sql7);
        if($ret7['total']) {
            $score = $ret7['total'];
        }


        $exchangeAverage = sprintf("%.2f", $orderCount / $userCount);//平均兑换次数（已付款订单数/兑换用户数）

        return array(
            'order_count' => $orderCount, //累计投注订单数量
            'order_users' => $userCount, //累计投注用户数量
            'order_score' => $score,//累计投注积分
            'order_average' =>$exchangeAverage//人均投注次数
        );
    }

}
new Daemon_Cron_Stats_Dailytotal();
