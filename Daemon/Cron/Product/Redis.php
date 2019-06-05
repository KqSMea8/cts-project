<?php
/**
 * 功能描述
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2018/1/8
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Product_Redis extends Daemon_Abstract{



    public function run()
    {
        ini_set('default_socket_timeout', -1);  //socket不超时
        set_time_limit(0);  //脚本不超时

        try {
            $num = 0;
            while(1) {
                $key = "exchange_product_detail";
                $redis = new Lib_Redis(Model_Const::REDIS_ALIAS_EXCHANGE, Lib_Redis::MODE_WRITE);
                //$redis->clean_queue($key);exit;
                $res_arr = $redis->read_queue($key, 3); //没有数据的情况下等待三秒
                $data = array();
                if($num >= 10) {
                    break;
                }
                if (is_array($res_arr) && !empty($res_arr[1])) {
                    //访问基础数据
                    $data = json_decode($res_arr[1], true);
                }

                if (empty($data) || empty($data['product_id'])) {
                    $num++;
                    continue;
                }


                Model_Stats::createStats($data);
            }

        }catch (Exception $e){
            Lib_Log::warning($e->getFile() . ":" . $e->getLine() . "#" . $e->getMessage());
            var_dump($e);
        }
    }
}

new Daemon_Cron_Product_Redis();  //注意和类名保持一致