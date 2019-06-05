<?php
/**
 * 处理队列推送用户扣减积分记录到大首页项目
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2019/03/11
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Luck_AccountLog extends Daemon_Abstract
{
    protected $cronName = 'LUCK_ACCOUNT_LOG';

    public function run()
    {
        $this->cronName = $this->cronName . time();
        try {
            $num = 0;
            while(1) {
                $key = "luck_account_log";
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

                if (empty($data) || empty($data['uid'])) {
                    $num++;
                    continue;
                }

                //请求大首页接口同步扣分
                $apiLuck      = new Api_Luck();
                $responseLuck = $apiLuck->accountLog($data['uid'],$data['seller_uid'],$data['gold'],$data['create_time']);
                if($responseLuck['code'] == 100000) {
                    continue;
                }else {
                    $apiLuck->accountLog($data['uid'],$data['seller_uid'],$data['gold'],$data['create_time']);
                }
            }
        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Luck_AccountLog();  //注意和类名保持一致