<?php
/**
 * 日报
 * User: cts(haoman@staff.weibo.com)
 * Date: 2018/12/05
 */


ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/stdafx.php'; //每个PHP脚本都要引入这个文件


class Daemon_Cron_Stats_Total extends Daemon_Abstract
{
    protected $start;
    protected $end;
    protected $date;
    protected $where_ctime;//创建时间
    protected $where_paytime;//支付时间
    protected $csv_path;
    const DBNAME = Model_Const::ALIAS_EXCHANGE;

    public function run()
    {
        $this->start = "2018-11-30 20:00:00";
        $this->end = "2018-12-27 23:59:59";

        $this->where_ctime = "create_time>='{$this->start}' and create_time<='{$this->end}'";
        $run_time = date('Y-m-d H:i:s');
        $this->csv_path = "{$_SERVER['SINASRV_DATA_DIR']}exchange_total.csv";



        //pv,uv
        $info = $this->getPvUv();
        $pv = $info['pv'];
        $uv = $info['uv'];

        //兑换用户数，总订单数，已付款订单，未付款订单，已发货订单，待发货订单,兑换总积分
        $orderInfo = $this->getOrderTotal();
        $userCount = $orderInfo['userCount']; //兑换用户数
        $orderCount = $orderInfo['orderCount']; //总订单数
        $payOrderCount = $orderInfo['payOrderCount']; //已付款订单
        $unpayOrderCount = $orderInfo['unpayOrderCount']; //未付款订单
        $unWriteOrderCount = $orderInfo['unWriteOrderCount']; //未填写订单数
        $sendOrderCount = $orderInfo['sendOrderCount']; //已发货订单
        $waitSendOrderCount = $orderInfo['waitSendOrderCount']; //待发货订单
        $confirmOrderCount = $orderInfo['confirmOrderCount']; //确认收货订单数
        $score = $orderInfo['score']; //兑换总积分
        $exchangeAverage = sprintf("%.2f", $payOrderCount / $userCount);//平均兑换次数（已付款订单数/兑换用户数）
        $exchangeRate = $this->progress($userCount, $uv,3);//兑换转化率（兑换用户数/UV）



        $txt = '';
        //流量 start
        $txt .= '兑换首页-uv: '. $uv . PHP_EOL;
        $txt .= '兑换首页-pv: '. $pv . PHP_EOL;
        //流量 end'

        //订单 start
        $txt .= '兑换用户数:'. $userCount . PHP_EOL;
        $txt .= '兑换转化率:'. $exchangeRate . PHP_EOL;
        $txt .= '总兑换积分数:'. $score . PHP_EOL;
        $txt .= '已付款订单数:'. $payOrderCount . PHP_EOL;
        $txt .= '未付款订单数:'. $unpayOrderCount . PHP_EOL;
        $txt .= '未填写订单数:'. $unWriteOrderCount . PHP_EOL;
        $txt .= '已发货订单数:'. $sendOrderCount . PHP_EOL;
        $txt .= '待发货订单数:'. $waitSendOrderCount . PHP_EOL;
        $txt .= '确认收货订单数:'. $confirmOrderCount . PHP_EOL;
        $txt .= '总订单数:'. $orderCount . PHP_EOL;
        $txt .= '平均兑换次数:'. $exchangeAverage . PHP_EOL;
        //订单 end'


        //CSV file
        if(!file_exists($this->csv_path)) {
            $csv_txt_arr = explode(PHP_EOL, $txt);
            $fp = fopen($this->csv_path, 'w');
            fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 写入BOM头，防止乱码
            fputcsv($fp, array('name', 'value'));
            foreach ($csv_txt_arr as $k => $line) {
                list($name, $value) = explode(':', $line);
                fputcsv($fp, array($name, $value));
            }
            fclose($fp);
        }
        //CSV file

        $txt .= PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.'程序开始时间: '. $run_time.PHP_EOL;
        $txt .= '程序结束时间: '. date('Y-m-d H:i:s').PHP_EOL;
        echo $txt;

        return;

    }

    private function getDatesBetweenTwoDays($startDate,$endDate){
        $dates = [];
        if(strtotime($startDate)>strtotime($endDate)){
            //如果开始日期大于结束日期，直接return 防止下面的循环出现死循环
            return $dates;
        }elseif($startDate == $endDate){
            //开始日期与结束日期是同一天时
            array_push($dates,$startDate);
            return $dates;
        }else{
            array_push($dates,$startDate);
            $currentDate = $startDate;
            do{
                $nextDate = date('Y-m-d', strtotime($currentDate.' +1 days'));
                array_push($dates,$nextDate);
                $currentDate = $nextDate;
            }while($endDate != $currentDate);
            return $dates;
        }
    }

    //pv,uv
    private function getPvUv() {
        $db = new Lib_Mysql(self::DBNAME);
        $pv = 0;
        $uv = 0;

        //获取两天之前的所有天
        $dates = $this->getDatesBetweenTwoDays($this->start,$this->end);
        foreach($dates as $key=>$date) {
            //分表按月
            $ym = date('Ym', strtotime($date));
            $month = $ym;
            $table = 'ex_stats_base_'. $month;
            $d = date('Ymd', strtotime($date));
            //pv
            $sqlPv = "select count(*) as pvs from {$table} where `action` = 'view_index' and `date` = '{$d}'";
            $retPv = $db->fetchOne($sqlPv);
            if($retPv['pvs']) {
                $pv += $retPv['pvs'];
            }

            //uv
            $sqlUv = "select count(DISTINCT(`uid`)) as uvs from {$table} where `action` = 'view_index' and `date` = '{$d}'";
            $retUv = $db->fetchOne($sqlUv);
            if($retUv['uvs']) {
                $uv += $retUv['uvs'];
            }
        }


        $result = array(
            'pv' => $pv,
            'uv' => $uv,
        );

        return $result;
    }


    //兑换用户数，总订单数，已付款订单，未付款订单，已发货订单，待发货订单,兑换总积分
    private function getOrderTotal() {
        $db = new Lib_Mysql(self::DBNAME);
        $userCount = 0; //兑换用户数
        $orderCount = 0; //总订单数
        $payOrderCount = 0; //已付款订单
        $unpayOrderCount = 0; //未付款订单
        $unWriteOrderCount = 0; ///未填写订单数
        $sendOrderCount = 0; //已发货订单
        $waitSendOrderCount = 0; //待发货订单
        $confirmOrderCount = 0; //确认收货
        $score = 0; //兑换总积分

        //兑换用户数
        $sql1 = "select count(DISTINCT(uid)) as total from `ex_product_order` where `status` = ".Model_Const::ORDER_STATUS_PAY." and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret1 = $db->fetchOne($sql1);
        if($ret1['total']) {
            $userCount = $ret1['total'];
        }

        //总订单数
        $sql2 = "select count(*) as total from `ex_product_order` where `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret2 = $db->fetchOne($sql2);
        if($ret2['total']) {
            $orderCount = $ret2['total'];
        }

        //已付款订单
        $sql3 = "select count(*) as total from `ex_product_order` where `status` = ".Model_Const::ORDER_STATUS_PAY." and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret3 = $db->fetchOne($sql3);
        if($ret3['total']) {
            $payOrderCount = $ret3['total'];
        }

        //未付款订单
        $sql4 = "select count(*) as total from `ex_product_order` where `status` = ".Model_Const::ORDER_STATUS_CREATE." and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret4 = $db->fetchOne($sql4);
        if($ret4['total']) {
            $unpayOrderCount = $ret4['total'];
        }

        //未填写订单数
        $sql5 = "select count(*) as total from `ex_product_order` where `status` = ".Model_Const::ORDER_STATUS_PAY." and `delivery_status` = ".Model_Const::ORDER_DELIVERY_STATUS_WAIT." and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret5 = $db->fetchOne($sql5);
        if($ret5['total']) {
            $unWriteOrderCount = $ret5['total'];
        }

        //已发货订单
        $sql6= "select count(*) as total from `ex_product_order` where `status` = ".Model_Const::ORDER_STATUS_PAY." and `delivery_status` = ".Model_Const::ORDER_DELIVERY_STATUS_SEND."  and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret6 = $db->fetchOne($sql6);
        if($ret6['total']) {
            $sendOrderCount = $ret6['total'];
        }

        //待发货订单
        $sql7= "select count(*) as total from `ex_product_order` where `status` = ".Model_Const::ORDER_STATUS_PAY." and `delivery_status` = ".Model_Const::ORDER_DELIVERY_STATUS_ADDRESS."  and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret7 = $db->fetchOne($sql7);
        if($ret7['total']) {
            $waitSendOrderCount = $ret7['total'];
        }

        //确认收货
        $sql8= "select count(*) as total from `ex_product_order` where `status` = ".Model_Const::ORDER_STATUS_PAY." and `delivery_status` = ".Model_Const::ORDER_DELIVERY_STATUS_CONFIRM."  and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret8 = $db->fetchOne($sql8);
        if($ret8['total']) {
            $confirmOrderCount = $ret8['total'];
        }

        //兑换总积分
        $sql9 = "select sum(`score`) as total from `ex_product_order` where `status` = ".Model_Const::ORDER_STATUS_PAY." and `create_time` >= '{$this->start}' and `create_time` <= '{$this->end}' ";
        $ret9 = $db->fetchOne($sql9);
        if($ret9['total']) {
            $score = $ret9['total'];
        }

        $result = array(
            'userCount' => $userCount, //兑换用户数
            'orderCount' => $orderCount, //总订单数
            'payOrderCount' => $payOrderCount, //已付款订单
            'unpayOrderCount' => $unpayOrderCount, //未付款订单
            'unWriteOrderCount' => $unWriteOrderCount,///未填写订单数
            'sendOrderCount' => $sendOrderCount, //已发货订单
            'waitSendOrderCount' => $waitSendOrderCount, //待发货订单
            'confirmOrderCount' => $confirmOrderCount, //确认收货
            'score' => $score, //消耗总积分
        );

        return $result;
    }

    private function progress($current, $total,$num = 2){
        $n = round($current/$total, $num) * 100;
        return $n . '%';
    }

}
new Daemon_Cron_Stats_Total();