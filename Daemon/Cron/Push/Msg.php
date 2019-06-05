<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2019/3/1
 * Time: 2:45 PM
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Push_Msg extends Daemon_Abstract
{
    protected $cronName = 'PUSH_MSG';

    public function run()
    {
        try {
           while(($msg = Model_PushMsg::pop()) !== false){
               echo $msg,"\n";
               if(empty($msg)){
                   continue;
               }
               Model_Cache::pushUserMsg($msg);
               //TODO:推送给大首页

           }
           echo "处理完毕\n";

        } catch (Exception $e) {
            $this->warning('Unknown Exception:' . $e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Push_Msg();  //注意和类名保持一致