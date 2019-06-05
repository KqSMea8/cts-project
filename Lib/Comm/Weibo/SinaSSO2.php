<?php
/**
 * @author: shiliang5
 * @version: 16/6/1
 * @copyright:copyright(2016) weibo.com all rights reserved
 */
if(!class_exists('Sso_Sdk_Client', false)){
    require_once T3PPATH.'/sso_sdk_client-0.6.22/client.php';
    require_once T3PPATH.'/sso_sdk_client-0.6.22/config.php';
    require_once T3PPATH.'/sso_sdk_client-0.6.22/user.php';
}

class Comm_Weibo_SinaSSO2 {
    private static $login_uid = null;
    private static $login_tid = null;

    /**
     * 从cookie中获取信息判断用户是否登陆
     */
    public static function get_login_uid () {
        if (null === self::$login_uid ) {
            $config = array(
                'service'            => 'sso',
                'entry'              => 'wbpay',
                'pin'                => '81f6d7ec2fecabb13c802ed51988edb3',
                'domain'             => '.weibo.com',
                'idc'                => 'yf',
                'ignore_verify_flag' => array("all"),
                'check_domain'       => false,
            );
            Sso_Sdk_Config::set_user_config($config);
            try {
                $sso_user_info = Sso_Sdk_Client::instance()->get_user();
            } catch (Exception $e) {
                throw new Comm_Weibo_Exception_SinaSSO("need relogin");
            }
            if ( $sso_user_info->is_status_normal() ) {
                self::$login_uid =  $sso_user_info->get_uid();
            }
            else {
                self::$login_uid = false;
            }
        }
        return self::$login_uid;
    }

    /**
     * 从cookie中获取基本用户信息，如uid
     */
    public static function get_user_info () {
        $uid = self::get_login_uid();
        if (!$uid ) {
            throw new Comm_Weibo_Exception_SinaSSO("need relogin");
        }
        return array(
            'uid' => $uid,
        );
    }

    /**
     * 从cookie中获取信息判断用户是否登陆
     */
    public static function get_login_tid () {
        if (null === self::$login_uid ) {
            $config = array(
                'service'            => 'sso',
                'entry'              => 'wbpay',
                'pin'                => '81f6d7ec2fecabb13c802ed51988edb3',
                'domain'             => '.weibo.com',
                'idc'                => 'yf',
                'ignore_verify_flag' => array("all"),
                'check_domain'       => false,
            );
            Sso_Sdk_Config::set_user_config($config);
            try {
                $sso_user_info = Sso_Sdk_Client::instance()->get_user();
                $obj = $sso_user_info->get_session();
                if (empty($obj)) {
                    return false;
                }
                $session_data = $obj->get_data();
            } catch (Exception $e) {
                throw new Comm_Weibo_Exception_SinaSSO("need relogin");
            }
            if ( $sso_user_info->is_status_normal() ) {
                self::$login_tid =  $session_data['tid'];
            }
            else {
                self::$login_tid = false;
            }
        }
        return self::$login_tid;
    }
}
