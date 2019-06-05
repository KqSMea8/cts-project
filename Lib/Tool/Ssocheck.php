<?php
include('/usr/local/sinasrv2/lib/php/sso/sdk/client/0.6.15/client.php');

/**
 * Tool_Ssocheck
 * sdk安全认证
 * @author    xiamengyu<mengyu5@staff.sina.com.cn>
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Tool_Ssocheck
{
    const WEIBO_PAY_ENTRY = 'weibo_pay';
    const WEIBO_PAY_PIN = 'adae8e24c087afc5c02d09d2a9a6a57e';

    public function checkUserSafity(){
        try{
            $idc = isset($_SERVER['SINASRV_ZONE_IDC'])?strtolower(str_replace(range(0,9), '', $_SERVER['SINASRV_ZONE_IDC'])):null;
            $arr = array(
                        'entry'              => self::WEIBO_PAY_ENTRY,
                        'service'            => self::WEIBO_PAY_ENTRY,
                        'pin'                => self::WEIBO_PAY_PIN,
                        'domain'             => '.weibo.com',
                        'idc'                => $idc,
                        'ignore_verify_flag' => array(),
                        'check_domain'       => true,
                        'scf_verify_flag'    => true,
                    );
            Sso_Sdk_Config::set_user_config($arr);

            $session = Sso_Sdk_Client::instance()->get_user()->get_session();

            //判断验证时间
            /*
            $now = strtotime('now');
            $sessionTime = $session->get_sverify_vtime();
            if (!empty($sessionTime) && $sessionTime != false){//有上回验证的时间
                Tool_Log::info('set session time:' . var_export($sessionTime, true));
                if ($now - $session->get_sverify_vtime() <= 43200){//上回验证时间不超过12小时
                    return true;
                }
            }
            */
            //只有https请求才能拿到
            $is_reliable = $session->get_session_credible();
            Tool_Log::info('withdraw reliable info:reliable=' . var_export($is_reliable, true) . '&&device=' . $_SERVER['HTTP_USER_AGENT']);
            if ($is_reliable === true){//可信用户
                $user = Sso_Sdk_Client::instance()->get_user();
                if ($user->is_status_normal()) {
                    //return $user->get_uid();
                    return true;
                }
            }else{
                Tool_Log::info('scf cookie:' . $_COOKIE['SCF']);
            }
            //从种的session中取
            /*
            try{
                if ($session->is_sverified()){//有种session
                    if($session->is_sverify_succ() === true){//种的session是成功的
                        Tool_Log::info('set session succ:uid=' . $session->get_uid());
                        return $session->get_uid();//成功返回uid
                    }else if ($session->is_sverify_fail() === true){
                        Tool_Log::info('set session failed:uid=' . $session->get_uid());
                        return 2;//种的session是失败的
                    }else if ($session->is_sverify_undefined() === true){
                        //未存验证session
                    }
                }
            }catch(Exception $e){
                Tool_log::info('no session info:uid=' . $session->uid);
            }
            */

            return false;
        }catch(Exception $e){
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }
}
