<?php
/**
 * 红包金额对应订单数统计
 * User: cts(haoman@staff.weibo.com)
 * Date: 2018/12/05
 */

ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/stdafx.php'; //每个PHP脚本都要引入这个文件


class Daemon_Cron_Dxj_Stats_Money extends Daemon_Abstract
{
    protected $start;
    protected $end;
    protected $date;
    const DBNAME = Model_Const::ALIAS_EXCHANGE;

    public function run()
    {
        $this->start = date('Y-m-d', strtotime('-1 days'))." 00:00:00";
        $this->end = date('Y-m-d', strtotime('-1 days'))." 23:59:59";
        $this->date = date('Ymd', strtotime('-1 days'));

        //当日下发红包订单总数，和分别统计对应金额订单总数
        $moneyInfo = $this->getMoneyDetail();

        $order1 = $moneyInfo['orderList'][1] ? $moneyInfo['orderList'][1] : 0;
        $order2 = $moneyInfo['orderList'][2] ? $moneyInfo['orderList'][2] : 0;
        $order3 = $moneyInfo['orderList'][3] ? $moneyInfo['orderList'][3] : 0;
        $order4 = $moneyInfo['orderList'][4] ? $moneyInfo['orderList'][4] : 0;
        $order5 = $moneyInfo['orderList'][5] ? $moneyInfo['orderList'][5] : 0;
        $order6 = $moneyInfo['orderList'][6] ? $moneyInfo['orderList'][6] : 0;

        $dataAry = array(
            '订单总数' => $moneyInfo['orderCount'],
            '1元订单数' => $order1,
            '1元订单占比' => $this->progress($order1,$moneyInfo['orderCount']),
            '2元订单数' => $order2,
            '2元订单占比' => $this->progress($order2,$moneyInfo['orderCount']),
            '3元订单数' => $order3,
            '3元订单占比' => $this->progress($order3,$moneyInfo['orderCount']),
            '5元订单数' => $order4,
            '5元订单占比' => $this->progress($order4,$moneyInfo['orderCount']),
            '10元订单数' => $order5,
            '10元订单占比' => $this->progress($order5,$moneyInfo['orderCount']),
            '30元订单数' => $order6,
            '30元订单占比' => $this->progress($order6,$moneyInfo['orderCount']),
        );


        //插入数据库
        try {
            Model_Stat::addMoneyDetail($this->date,json_encode($dataAry));

            $users = array(
                'haoman@staff.weibo.com',
            );
            Tool_Mail::sendMail("分金额统计订单执行成功！",$users);

        }catch(Exception $e) {
            $this->warning("DXJ_STATS_MONEY error:". json_encode($e));
        }
        return;

    }

    //当日下发红包订单总数，分别统计对应金额订单总数
    private function getMoneyDetail() {
        $db = new Lib_Mysql(self::DBNAME);
        $orderCount = 0; //总订单数
        $orderList = array();

        //总订单数
        $sql1 = "select count(*) as total from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `consume_time` >= '{$this->start}' and `consume_time` <= '{$this->end}' ";
        $ret1 = $db->fetchOne($sql1);
        if($ret1['total']) {
            $orderCount = $ret1['total'];
        }

        //分别统计对应金额订单总数
        $sql2 = "select count(*) as total , `product_id` from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `consume_time` >= '{$this->start}' and `consume_time` <= '{$this->end}' group by `product_id`";
        $ret2 = $db->fetchAll($sql2);
        if($ret2) {
            foreach($ret2 as $k=>$rowAry) {
                if($rowAry['total']) {
                    $orderList[$rowAry['product_id']] = $rowAry['total'];
                }else {
                    $orderList[$rowAry['product_id']] = 0;
                }
            }
        }

        $result = array(
            'orderCount' => $orderCount, //总订单数
            'orderList' => $orderList,
        );

        return $result;
    }



    private function progress($current, $total,$num = 3){
        $n = round($current/$total, $num) * 100;
        return $n . '%';
    }

}
new Daemon_Cron_Dxj_Stats_Money();