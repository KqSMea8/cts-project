<?php
/**
 * 历史数据推送给大首页
 * @author: cts <zhongfeng@staff.weibo.com>
 * @date: 2019/03/11
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Tool_AccountLog extends Daemon_Abstract
{
    protected $cronName = 'TOOL_ACCOUNT_LOG';

    const BATCH = 1000;

    public function run()
    {
        $this->cronName = $this->cronName . time();
        try {
            $file = "/tmp/out_order_history";
            $id = 0;
            while(true) {
                try {
                    $db = new Lib_Mysql(Model_Const::ALIAS_EXCHANGE);
                    //查询所有兑换用户
                    $sql = "select * from `ex_money_order` where order_id > {$id} and create_time >= '2018-12-29 00:00:00' and create_time <= '2019-05-09 10:20:00' limit  " . self::BATCH;
                    echo $sql . "\n";
                    $ret = $db->fetchAll($sql);
                    if ($ret) {
                        foreach ($ret as $key => $row) {
                            //请求大首页接口同步扣分
                            $apiLuck = new Api_Luck();
                            $responseLuck = $apiLuck->accountLog($row['uid'], Model_JF::MID_DH, $row['score'], $row['create_time']);
                            if ($responseLuck['code'] == 100000) {
                                continue;
                            } else {
                                $apiLuck->accountLog($row['uid'], Model_JF::MID_DH, $row['score'], $row['create_time']);
                            }
                        }
                        $id = $row['order_id'];
                        file_put_contents($file, $id . "\r\n");
                    } else {
                       echo '111';
                        break;
                    }
                }catch(Exception $e) {
                    print_r($e);
                }
            }
        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Tool_AccountLog();  //注意和类名保持一致