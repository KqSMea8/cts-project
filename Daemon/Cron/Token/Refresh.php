<?php
/**
 * 功能描述
 * @author: zhongfeng <zhongfeng@staff.weibo.com>
 * @date: 2018/09/05
 */
require_once dirname(dirname(dirname(__FILE__))) . '/../stdafx.php';//每个PHP脚本都要引入这个文件
class Daemon_Cron_Token_Refresh extends Daemon_Abstract
{
    protected $cronName = 'TOKEN_REFRESH';

    public function run()
    {
        try {
            $appKey = Model_Const::APP_SOURCE_EXCHANGE;
            $exist = json_decode(Data_Etc::getAppToken($appKey), true);

            if($exist['expired_time'] - time() < 86400) {
                $apiWeibo = new Api_Weibo();

                $appToken = $apiWeibo->getTAuth($appKey);
                var_dump($appToken);
                $appToken['expired_time'] = time() + 172800;
                if (isset($appToken['tauth_token']) && !empty($appToken['tauth_token'])) {
                    $this->warning("App Token Update SUCCESS:" . $appKey . " RES:" . is_array($appToken) ? json_encode($appToken) : $appToken);
                    $res = Data_Etc::updateEtc(json_encode($appToken), Model_Const::APP_TOKEN_ETC_NAME, Model_Const::APP_TOKEN_ETC_KEY1, $appKey);
                    var_dump($res);
                    if (!$res && $res != 0) {
                        $this->warning("App Token Update Failed:" . $appKey . " RES:" . is_array($appToken) ? json_encode($appToken) : $appToken);
                        echo $appKey . " Update Failed", "\n";
                    } else {
                        echo $appKey . " Update SUCCESS " . $appToken['token'], "\n";
                    }
                } else {
                    $this->warning("App Token GET Failed:" . $appKey . " RES:" . is_array($appToken) ? json_encode($appToken) : $appToken);
                    echo $appKey . " GET Failed", "\n";
                }
            }else{
                //$this->warning("App Token not expire");
                echo "not expire \n";
            }
        } catch (Exception $e) {
            $this->warning("Unknown Exception:".$e->getMessage());
            $this->exceptions[] = $e;
        }
    }
}
new Daemon_Cron_Token_Refresh();  //注意和类名保持一致