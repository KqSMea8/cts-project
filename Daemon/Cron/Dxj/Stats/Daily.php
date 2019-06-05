<?php
/**
 * 日报
 * User: cts(haoman@staff.weibo.com)
 * Date: 2018/12/05
 */


ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/stdafx.php'; //每个PHP脚本都要引入这个文件


class Daemon_Cron_Dxj_Stats_Daily extends Daemon_Abstract
{
    protected $start;
    protected $end;
    protected $date;
    protected $where_paytime;//支付时间
    protected $csv_path;
    protected $ym;
    const DBNAME = Model_Const::ALIAS_EXCHANGE;

    public function run()
    {
        $this->start = date('Y-m-d', strtotime('-1 days'))." 00:00:00";
        $this->end = date('Y-m-d', strtotime('-1 days'))." 23:59:59";
        $this->date = date('Ymd', strtotime('-1 days'));
        $this->ym =  date('Ym', strtotime('-1 days'));

        //$this->start = "2019-01-15 00:00:00";
        //$this->end = "2019-01-15 23:59:59";
        //$this->date = "20190115";
        //$this->ym = "201901";

        $run_time = date('Y-m-d H:i:s');
        $this->csv_path = "{$_SERVER['SINASRV_DATA_DIR']}exchange_bonus_daily_{$this->date}.csv";


        //pv,uv
        $info = $this->getPvUv();
        $pv = $info['pv'];
        $uv = $info['uv'];

        //兑红包用户数，兑红包订单数，兑红包消耗积分输，兑换转化率，人均兑红包次数，手续费，下发红包金额，审核拒绝红包金额
        $orderInfo = $this->getOrderTotal();
        $userCount = $orderInfo['userCount']; //兑换用户数
        $orderCount = $orderInfo['orderCount']; //总订单数
        $score = $orderInfo['score']; //兑换总积分
        $poundageScore = $orderInfo['poundageScore']; //手续费
        $moneyBouns = $orderInfo['moneyBouns']; //下发红包金额
        $moneyReject = $orderInfo['moneyReject']; //审核拒绝红包金额
        $exchangeAverage = sprintf("%.2f", $orderCount / $userCount);//平均兑换次数（订单数/兑换用户数）
        $exchangeRate = $this->progress($userCount, $uv, 3);//兑换转化率（兑换用户数/UV）


        //新用户数，新用户占比，新投注用户arpu，新投注用户asp
        $orderInfoNew = $this->getNewUser($this->date);

        //累计
        $userCountTotal = $this->getHistoryData();

        $txt = '';
        //流量 start
        $txt .= '兑红包首页-uv: ' . $uv . PHP_EOL;
        $txt .= '兑红包首页-pv: ' . $pv . PHP_EOL;
        //流量 end'

        //订单 start
        $txt .= '兑红包用户数:' . $userCount . PHP_EOL;
        $txt .= '兑红包订单数:' . $orderCount . PHP_EOL;
        $txt .= '兑红包消耗积分数:' . $score . PHP_EOL;
        $txt .= '兑红包手续费:' . $poundageScore . PHP_EOL;
        $txt .= '下发红包金额:' . $moneyBouns . PHP_EOL;
        $txt .= '审核拒绝红包金额:' . $moneyReject . PHP_EOL;
        $txt .= '兑换转化率:' . $exchangeRate . PHP_EOL;
        $txt .= '人均兑红包次数:' . $exchangeAverage . PHP_EOL;
        //订单 end'

        //新用户 start
        $txt .= '新用户数: ' . $orderInfoNew['pay_users'] . PHP_EOL;
        $txt .= '新用户占比: ' . $this->progress($orderInfoNew['pay_users'], $userCount) . PHP_EOL;
        $txt .= '新用户兑红包积分数: ' . $orderInfoNew['pay_score'] . PHP_EOL;
        $txt .= '新用户兑红包积分占比: ' . $this->progress($orderInfoNew['pay_score'], $score) . PHP_EOL;
        //新用户 end

        //累计 start
        $txt .= '累计兑红包用户:' . $userCountTotal . PHP_EOL;
        //累计 end


        //CSV file
        if (!file_exists($this->csv_path)) {
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

        $txt .= PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . '程序开始时间: ' . $run_time . PHP_EOL;
        $txt .= '程序结束时间: ' . date('Y-m-d H:i:s') . PHP_EOL;
        echo $txt;


        $this->sendMail($txt);
        return;

    }


    private function sendMail($content){
        $users = array(
            'molin@staff.weibo.com',
            'yangshuai1@staff.weibo.com',
            'mengjia1@staff.weibo.com',
            'zhangdi3@staff.weibo.com',
            'libo16@staff.weibo.com',
            'liubin10@staff.weibo.com',
            'dongyang1@staff.weibo.com',
            'geyue@staff.weibo.com',
            'fuyu@staff.weibo.com',
            'yuqi6@staff.weibo.com',
            'zhongfeng@staff.weibo.com',
            'haoman@staff.weibo.com',
            'xiaoguang15@staff.weibo.com',
            'caihong@staff.weibo.com',
            'zhaohe1@staff.weibo.com',
            'baoying3@staff.weibo.com',
            'luyang5@staff.weibo.com',
            'xiangmei@staff.weibo.com',
            'qilong3@staff.weibo.com',
            'dali@staff.weibo.com',
            'xiaoyu28@staff.weibo.com',
            'moci@staff.weibo.com',
            'gaolong1@staff.weibo.com',
            'xutao3@staff.weibo.com',
            'yangfan26@staff.sina.com.cn',
            'lihao9@staff.weibo.com',
            'gaolong1@staff.weibo.com',
            'jiaqi20@staff.weibo.com'
        );
        /*$users = array(
            'haoman@staff.weibo.com',
        );*/
        while(1){
            $log_ret = Tool_Mail::sendMailWithAtta($content, $users, date('Ymd') . '报表', false, 'daily_notice_money', array(array('path' => $this->csv_path, 'name' => 'exchange_bonus_daily_'.$this->date.'.csv')));
            var_dump($log_ret);
            Lib_Log::info('daily Tool_Mail result: ' . $log_ret);

            if($log_ret === true){
                break;
            }
            sleep(60);
        }
    }


    //pv,uv
    private function getPvUv() {
        $db = new Lib_Mysql(self::DBNAME);
        $pv = 0;
        $uv = 0;
        //分表按月
        $month = $this->ym;
        $table = 'ex_stats_base_'. $month;

        //pv
        $sqlPv = "select count(*) as pvs from {$table} where `action` = 'view_redbag' and `date` = '{$this->date}'";
        $retPv = $db->fetchOne($sqlPv);
        if($retPv['pvs']) {
            $pv = $retPv['pvs'];
        }

        //uv
        $sqlUv = "select count(DISTINCT(`uid`)) as uvs from {$table} where `action` = 'view_redbag' and `date` = '{$this->date}'";
        $retUv = $db->fetchOne($sqlUv);
        if($retUv['uvs']) {
            $uv = $retUv['uvs'];
        }

        $result = array(
            'pv' => $pv,
            'uv' => $uv,
        );

        return $result;
    }


    //兑换用户数，总订单数，兑换总积分,手续费，下发红包金额，审核拒绝红包金额
    private function getOrderTotal() {
        $db = new Lib_Mysql(self::DBNAME);
        $userCount = 0; //兑换用户数
        $orderCount = 0; //总订单数
        $score = 0; //兑换总积分
        $poundageScore = 0; //手续费
        $moneyBouns = 0; //下发红包金额
        $moneyReject = 0; //审核拒绝红包金额


        //兑换用户数
        $sql1 = "select count(DISTINCT(uid)) as total from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `consume_time` >= '{$this->start}' and `consume_time` <= '{$this->end}' ";
        $ret1 = $db->fetchOne($sql1);
        if($ret1['total']) {
            $userCount = $ret1['total'];
        }

        //总订单数
        $sql2 = "select count(*) as total from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `consume_time` >= '{$this->start}' and `consume_time` <= '{$this->end}' ";
        $ret2 = $db->fetchOne($sql2);
        if($ret2['total']) {
            $orderCount = $ret2['total'];
        }

        //兑换总积分
        $sql3 = "select sum(`score`) as total from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `consume_time` >= '{$this->start}' and `consume_time` <= '{$this->end}' ";
        $ret3 = $db->fetchOne($sql3);
        if($ret3['total']) {
            $score = $ret3['total'];
        }

        //手续费
        $sql4 = "select sum(`poundage`) as total from `ex_money_order` where `status` > ".Model_Const::MONEY_ORDER_STATUS_CLOSE." and  `consume_time` >= '{$this->start}' and `consume_time` <= '{$this->end}' ";
        $ret4 = $db->fetchOne($sql4);
        if($ret4['total']) {
            $poundageScore = $ret4['total'];
        }

        //下发红包金额
        $sql5 = "select sum(`money`) as total from `ex_money_order` where  (`status` = ".Model_Const::MONEY_ORDER_STATUS_BONUS." or `status` = ".Model_Const::MONEY_ORDER_STATUS_VERIFY.") and `consume_time` >='{$this->start}' and `consume_time` <= '{$this->end}' ";
        $ret5 = $db->fetchOne($sql5);
        if($ret5['total']) {
            $moneyBouns = $ret5['total'];
        }

        //审核拒绝红包金额
        $sql6 = "select sum(`money`) as total from `ex_money_order` where `status` = " . Model_Const::MONEY_ORDER_STATUS_REJECT ." and `consume_time` >='{$this->start}' and `consume_time` <= '{$this->end}' ";
        $ret6 = $db->fetchOne($sql6);
        if($ret6['total']) {
            $moneyReject = $ret6['total'];
        }

        $result = array(
            'userCount' => $userCount, //兑换用户数
            'orderCount' => $orderCount, //总订单数
            'score' => $score, //消耗总积分
            'poundageScore' => $poundageScore,//手续费
            'moneyBouns' => $moneyBouns,//下发红包金额
            'moneyReject' => $moneyReject,//审核拒绝红包金额
        );

        return $result;
    }

    //累计计算
    private function getHistoryData(){
        $db = new Lib_Mysql(self::DBNAME);
        $userCount = 0 ;
        //兑换用户数
        $sql1 = "select count(DISTINCT(uid)) as total from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_PAY." and `consume_time` >= '2019-01-15 00:00:00' and `consume_time` <= '{$this->end}' ";
        $ret1 = $db->fetchOne($sql1);
        if($ret1['total']) {
            $userCount = $ret1['total'];
        }
        return $userCount;
    }

    //新用户计算
    private function getNewUser($date){
        $redis_r = new Lib_Redis(self::DBNAME, Lib_Redis::MODE_WRITE);
        $key_t = 'exchange_bonus_stat_str_new_users'.$date;//当天新用户总数
        $ret = $redis_r->get($key_t);
        $ret = json_decode($ret, true);
        return $ret;
    }

    private function progress($current, $total,$num = 2){
        $n = round($current/$total, $num) * 100;
        return $n . '%';
    }

}
new Daemon_Cron_Dxj_Stats_Daily();