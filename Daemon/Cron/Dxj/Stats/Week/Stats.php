<?php
/**
 * 日报
 * User: cts(haoman@staff.weibo.com)
 * Date: 2018/12/05
 */


ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/stdafx.php'; //每个PHP脚本都要引入这个文件


class Daemon_Cron_Dxj_Stats_Week_Stats extends Daemon_Abstract
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
        $this->start = date('Y-m-d', strtotime('-7 days'));
        $this->end = date('Y-m-d', strtotime('-1 days'));
        $this->date = date('Ymd', strtotime('-1 days'));

        $this->where_ctime = "create_time>='{$this->start} 00:00:00' and create_time<='{$this->end} 23:59:59'";
        $run_time = date('Y-m-d H:i:s');
        $this->csv_path = "{$_SERVER['SINASRV_DATA_DIR']}exchange_bonus_week_{$this->date}.csv";



        //pv,uv
        $info = $this->getPvUv();
        $pv = $info['pv'];
        $uv = $info['uv'];

        //兑红包用户数，兑红包订单数，兑红包消耗积分输，兑换转化率，人均兑红包次数
        $orderInfo = $this->getOrderTotal();
        $userCount = $orderInfo['userCount']; //兑换用户数
        $orderCount = $orderInfo['orderCount']; //总订单数
        $score = $orderInfo['score']; //兑换总积分
        $exchangeAverage = sprintf("%.2f", $orderCount / $userCount);//平均兑换次数（已付款订单数/兑换用户数）
        $exchangeRate = $this->progress($userCount, $uv,3);//兑换转化率（兑换用户数/UV）


        //新用户数，新用户占比，新投注用户arpu，新投注用户asp
        $orderInfoNew = $this->getNewUser($this->date);


        $txt = '';
        //流量 start
        $txt .= '兑红包首页-uv: '. $uv . PHP_EOL;
        $txt .= '兑红包首页-pv: '. $pv . PHP_EOL;
        //流量 end'

        //订单 start
        $txt .= '兑红包用户数:'. $userCount . PHP_EOL;
        $txt .= '兑红包订单数:'. $orderCount . PHP_EOL;
        $txt .= '兑红包消耗积分数:'. $score . PHP_EOL;
        $txt .= '兑换转化率:'. $exchangeRate . PHP_EOL;
        $txt .= '人均兑红包次数:'. $exchangeAverage . PHP_EOL;
        //订单 end'

        //新用户 start
        $txt .= '新用户数: ' . $orderInfoNew['pay_users'] .PHP_EOL;
        $txt .= '新用户占比: ' . $this->progress($orderInfoNew['pay_users'], $userCount) .PHP_EOL;
        $txt .= '新用户兑红包积分数: ' . $orderInfoNew['pay_score'].PHP_EOL;
        $txt .= '新用户兑红包积分占比: ' . $this->progress($orderInfoNew['pay_score'], $score).PHP_EOL;
        //新用户 end


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
        $this->sendMail($txt);
        return;

    }


    private function sendMail($content){
        $users = array(
            'haoman@staff.weibo.com',
            'zhongfeng@staff.weibo.com',
            'yangfan26@staff.sina.com.cn',
            'libo16@staff.weibo.com',
            'moci@staff.weibo.com',
            'xutao3@staff.weibo.com',
            'dongyang1@staff.weibo.com',
            'xiaoguang15@staff.weibo.com',
            'lihao9@staff.weibo.com',
            'gaolong1@staff.weibo.com',
            'jiaqi20@staff.weibo.com'
        );
        while(1){
            $log_ret = Tool_Mail::sendMailWithAtta($content, $users, date('Ymd') . '报表', false, 'week_notice_money', array(array('path' => $this->csv_path, 'name' => 'exchange_bonus_week_'.$this->date.'.csv')));
            var_dump($log_ret);
            Lib_Log::info('daily Tool_Mail result: ' . $log_ret);

            if($log_ret === true){
                break;
            }
            sleep(60);
        }
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
            $sqlPv = "select count(*) as pvs from {$table} where `action` = 'view_redbag' and `date` = '{$d}'";
            $retPv = $db->fetchOne($sqlPv);
            if($retPv['pvs']) {
                $pv += $retPv['pvs'];
            }

            //uv
            $sqlUv = "select count(DISTINCT(`uid`)) as uvs from {$table} where `action` = 'view_redbag' and `date` = '{$d}'";
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
        $score = 0; //兑换总积分

        //兑换用户数
        $sql1 = "select count(DISTINCT(uid)) as total from `ex_money_order` where  `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `create_time` >= '{$this->start} 00:00:00' and `create_time` <= '{$this->end} 23:59:59' ";
        $ret1 = $db->fetchOne($sql1);
        if($ret1['total']) {
            $userCount = $ret1['total'];
        }

        //总订单数
        $sql2 = "select count(*) as total from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `create_time` >= ' {$this->start} 00:00:00' and `create_time` <= '{$this->end} 23:59:59' ";
        $ret2 = $db->fetchOne($sql2);
        if($ret2['total']) {
            $orderCount = $ret2['total'];
        }

        //兑换总积分
        $sql7 = "select sum(`score`) as total from `ex_money_order` where  `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `create_time` >= '{$this->start} 00:00:00' and `create_time` <= '{$this->end} 23:59:59' ";
        $ret7 = $db->fetchOne($sql7);
        if($ret7['total']) {
            $score = $ret7['total'];
        }

        $result = array(
            'userCount' => $userCount, //兑换用户数
            'orderCount' => $orderCount, //总订单数
            'score' => $score, //消耗总积分
        );

        return $result;
    }


    //新用户计算
    private function getNewUser($date){
        $redis_r = new Lib_Redis(self::DBNAME, Lib_Redis::MODE_WRITE);
        $key_t = 'exchange_bonus_stat_str_new_users_week'.$date;//当天新用户总数
        $ret = $redis_r->get($key_t);
        $ret = json_decode($ret, true);
        return $ret;
    }

    private function progress($current, $total,$num = 2){
        $n = round($current/$total, $num) * 100;
        return $n . '%';
    }

}
new Daemon_Cron_Dxj_Stats_Week_Stats();