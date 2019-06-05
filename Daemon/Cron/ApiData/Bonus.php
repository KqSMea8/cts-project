<?php
/**
 * 兑红包api提供的统计数据
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2019/01/29
 */

ini_set('memory_limit', '2048M');
set_time_limit(0);

require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_ApiData_Bonus extends Daemon_Abstract
{
    protected $cronName = 'APIDATA_BONUS';

    public function run()
    {
        try {

            $start = date('Y-m-d', strtotime('-1 days'))." 00:00:00";
            $end = date('Y-m-d', strtotime('-1 days'))." 23:59:59";
            $date = date('Ymd', strtotime('-1 days'));


            $userCount = 0; //当日用户
            $orderCount = 0; //当日订单
            $totalScore = 0; //当日兑换总积分数
            $totalMoney = 0; //当日下发红包（元）
            $successScore = 0;//当日成功兑红包积分数
            $poundageScore = 0;//手续费积分
            $rejectScore = 0; //审核失败积分
            $validateScore = 0;//待审核积分
            $acceptOrder = 0;//兑红包当日通过订单

            $totalScoreAll = 0;//累计兑红包参与积分数
            $successScoreAll = 0;//累计成功兑换积分数
            $acceptOrderAll = 0;//累计至今通过的订单
            $orderCountAll = 0; //累计至今兑红包订单
            $totalMoneyAll = 0;//累计下发红包（元）


            //当日用户
            $sql1 = "select count(DISTINCT(uid)) as total from `ex_money_order` where `create_time` >= '{$start}' and `create_time` <= '{$end}' ";
            $ret1 = Data_Exchange::fetchOne($sql1);
            if($ret1['total']) {
                $userCount = $ret1['total'];
            }

            //总订单数
            $sql2 = "select count(*) as total from `ex_money_order` where `create_time` >= '{$start}' and `create_time` <= '{$end}' ";
            $ret2 = Data_Exchange::fetchOne($sql2);
            if($ret2['total']) {
                $orderCount = $ret2['total'];
            }

            //当日参与总积分
            $sql3 = "select sum(`score`) as total from `ex_money_order` where `status` > ".Model_Const::MONEY_ORDER_STATUS_CLOSE." and `create_time` >= '{$start}' and `create_time` <= '{$end}' ";
            $ret3 = Data_Exchange::fetchOne($sql3);
            if($ret3['total']) {
                $totalScore = $ret3['total'];
            }

            //当日下发红包（元），成功兑红包积分数，成功的手续费积分
            $sql4 = "select sum(money) as `total_money` ,sum(`score`) as `total_score` , sum(`poundage`) as `total_poundage`  from `ex_money_order` where  (`status` = ".Model_Const::MONEY_ORDER_STATUS_BONUS." or `status` = ".Model_Const::MONEY_ORDER_STATUS_VERIFY.")  and `create_time` >= '{$start}' and `create_time` <= '{$end}' ";
            $ret4 = Data_Exchange::fetchOne($sql4);
            if($ret4) {
                $totalMoney = $ret4['total_money'];
                $successScore = $ret4['total_score']-$ret4['total_poundage'];
            }

            $successScoreRate = $this->progress($successScore, $totalScore,2);//当日成功兑换红包积分占比

            //手续费积分
            $sql6 = "select sum(`poundage`) as total from `ex_money_order` where `status` > ".Model_Const::MONEY_ORDER_STATUS_CLOSE." and  `create_time` >= '{$start}' and `create_time` <= '{$end}' ";
            $ret6 = Data_Exchange::fetchOne($sql6);
            if($ret6['total']) {
                $poundageScore = $ret6['total'];
            }

            $poundageScoreRate = $this->progress($poundageScore, $totalScore,2);//当日手续费积分占比

            //审核失败积分
            $sql7 = "select sum(`score`) as total , sum(`poundage`) as `total_poundage` from `ex_money_order` where `status` = ".Model_Const::MONEY_ORDER_STATUS_REJECT." and  `create_time` >= '{$start}' and `create_time` <= '{$end}' ";
            $ret7 = Data_Exchange::fetchOne($sql7);
            if($ret7['total']) {
                $rejectScore = $ret7['total'] - $ret7['total_poundage'];
            }

            $rejectScoreRate = $this->progress($rejectScore, $totalScore,2);//当日审核失败积分占比

            //待审核积分
            $sql8 = "select sum(`score`) as total , sum(`poundage`) as `total_poundage` from `ex_money_order` where `status` = ".Model_Const::MONEY_ORDER_STATUS_VALIDATE." and  `create_time` >= '{$start}' and `create_time` <= '{$end}' ";
            $ret8 = Data_Exchange::fetchOne($sql8);
            if($ret8['total']) {
                $validateScore = $ret8['total'] - $ret8['total_poundage'];
            }

            $validateScoreRate = $this->progress($validateScore, $totalScore,2);//当日待审核积分占比

            //当日红包价值
            $bonusValue = round($successScore/$totalScore, 2);

            //累计成功兑换积分数
            $sql9 = "select sum(`score`) as `total`  , sum(`poundage`) as `total_poundage` from `ex_money_order`  where (`status` = ".Model_Const::MONEY_ORDER_STATUS_BONUS." or `status` = ".Model_Const::MONEY_ORDER_STATUS_VERIFY.") and  `create_time` >= '2019-01-15 00:00:00' and `create_time` <= '{$end}' ";
            $ret9 = Data_Exchange::fetchOne($sql9);
            if($ret9) {
                $successScoreAll = $ret9['total'] -  $ret9['total_poundage'];
            }

            //累计兑红包参与积分数
            $sql10 = "select sum(`score`) as total from `ex_money_order` where  `create_time` >= '2019-01-15 00:00:00' and `create_time` <= '{$end}' ";
            $ret10 = Data_Exchange::fetchOne($sql10);
            if($ret10['total']) {
                $totalScoreAll = $ret10['total'];
            }

            //累计红包价值 = 累计成功兑换积分数/累计兑红包参与积分数
            $bonusValueAll = round($successScoreAll/$totalScoreAll, 2);


            //兑红包当日通过订单
            $sql11 = "select count(*) as total from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_ACCEPT." and  `create_time` >= '{$start}' and `create_time` <= '{$end}' ";
            $ret11 = Data_Exchange::fetchOne($sql11);
            if($ret11['total']) {
                $acceptOrder = $ret11['total'];
            }

            //兑红包当日通过率 = 兑红包当日通过订单/兑红包当日订单
            $acceptRate = $this->progress($acceptOrder, $orderCount,2);

            //上线至今通过的订单
            $sql12 = "select count(*) as total from `ex_money_order` where `status` >= ".Model_Const::MONEY_ORDER_STATUS_ACCEPT." and  `create_time` >= '2019-01-15 00:00:00' and `create_time` <= '{$end}' ";
            $ret12 = Data_Exchange::fetchOne($sql12);
            if($ret12['total']) {
                $acceptOrderAll = $ret12['total'];
            }

            //上线至今兑红包订单
            $sql13 = "select count(*) as total from `ex_money_order` where `create_time` >= '2019-01-15 00:00:00' and `create_time` <= '{$end}' ";
            $ret13 = Data_Exchange::fetchOne($sql13);
            if($ret13['total']) {
                $orderCountAll = $ret13['total'];
            }

            //累计兑红包通过率 = 上线至今通过的订单/上线至今兑红包订单
            $acceptRateAll = $this->progress($acceptOrderAll, $orderCountAll,2);

            //累计下发红包（元）
            $sql14 = "select sum(money) as `total_money`  from `ex_money_order` where  (`status` = ".Model_Const::MONEY_ORDER_STATUS_BONUS." or `status` = ".Model_Const::MONEY_ORDER_STATUS_VERIFY.")  and `create_time` >= '2019-01-15 00:00:00' and `create_time` <= '{$end}' ";
            $ret14 = Data_Exchange::fetchOne($sql14);
            if($ret14['total_money']) {
                $totalMoneyAll = $ret14['total_money'];
            }

            //当日arpu
            $arpu =  $this->arpu($successScore,$userCount);

            //当日asp
            $asp =  $this->asp($successScore,$orderCount);

            $dataAry = array(
                //'totalScore' => $totalScore,
                'total_money' =>$totalMoney, //当日下发红包（元）
                'success_score' =>$successScore,//成功兑红包积分数
                'success_score_rate' =>$successScoreRate,//成功兑红包占比
                'poundage_score' => $poundageScore,//手续费积分
                'poundage_score_rate' => $poundageScoreRate,//手续分积分占比
                'reject_score' => $rejectScore,//审核失败积分
                'reject_score_rate' => $rejectScoreRate,//审核失败积分占比
                'validate_score' => $validateScore,//待审积分
                'validate_score_rate' => $validateScoreRate,//待审积分占比
                'bonus_value' => $bonusValue,//当日兑红包价值
                'bonus_value_all' => $bonusValueAll,//累计兑红包价值
                'accept_rate' => $acceptRate,//兑红包当日通过率
                'accept_rate_all' => $acceptRateAll,//累计兑红包通过率
                'total_money_all' => $totalMoneyAll,//累计下发红包（元）
                'arpu' => $arpu,//arpu
                'asp' => $asp,//asp
            );

            $redis = new Lib_Redis(Model_Const::REDIS_ALIAS_EXCHANGE, Lib_Redis::MODE_WRITE);
            $key = 'api_data_bonus_'.$date;
            $redis->set($key, json_encode($dataAry));

        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }


    private function progress($current, $total,$num = 2){
        $n = round($current/$total, $num) * 100;
        return $n . '%';
    }

    private function arpu($gold, $user_count){
        return round($gold/$user_count, 2);
    }
    private function asp($gold, $count){
        return round($gold/$count, 2);
    }
}
new Daemon_Cron_ApiData_Bonus();  //注意和类名保持一致