<?php
//T+1
ini_set('memory_limit', '1024M');
set_time_limit(0);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/stdafx.php'; //每个PHP脚本都要引入这个文件

/**
 * 日报
 */
class Daemon_Cron_Stats_Dailyrdata extends Daemon_Abstract
{
    public function run()
    {
        $date = date('Ymd', strtotime('-1 days'));
        $file = "{$_SERVER['SINASRV_DATA_DIR']}exchange_daily_{$date}.csv";
        $dailyDataAry = array();
        $result = array();
        if(file_exists($file)) {
            $f = fopen($file,'r');
            //读取csv文件
            while ($data = fgetcsv($f)) { //每次读取CSV里面的一行内容
                //print_r($data); //此为一个数组，要获得每一个数据，访问数组下标即可
                $dailyDataAry[] = $data;
            }

            if($dailyDataAry) {
                foreach($dailyDataAry as $key=>$rowAry) {
                    if($key != 0 && !empty($rowAry[0])) {
                        $result[$rowAry[0]] = $rowAry[1];
                    }
                }

                //插入数据库
                try {
                    Model_Stat::addInfo($date,json_encode($result));
                }catch(Exception $e) {
                    print_r($e);
                }
            }
        }

        return;
    }




}
new Daemon_Cron_Stats_Dailyrdata();
