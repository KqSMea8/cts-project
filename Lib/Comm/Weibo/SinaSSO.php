<?php

/*
 * 重要： 由于动态平台已经内置SSOClient包，所以如下require进来的文件仅当非动态平台环境时可用。
 * 如需要修改，请确认您对SSOClient的用法明晰，并自行对修改负责。
 * 
 * 其他情况，请自行加载 SSOConfig
 * By Rodin <luodan@staff.sina.com.cn>
 */
if(!class_exists('SSOClient', false)){
	require_once PATH_THIRD_LIB.'/sinasso/SSOWeiboClient.php';
	require_once PATH_THIRD_LIB.'/sinasso/SSOWeiboCookie.php';
	require_once PATH_ROOT.'/Config/SSOConfig.php';
}

/**
 * 
 * @package Common
 * @subpackage Weibo
 * @author weibo.com, php team
 * @copyright (c) 2011, PHP Team, tech.intra.weibo.com
 */
class Comm_Weibo_SinaSSO{
    /**
     * @var object SSOWeiboClient 实例
     */
    private static $sso_client = null;
    
    /**
     * @var bool 是否登陆
     */
    private static $is_logon = null;
    
    /**
     * 从cookie中获取信息判断用户是否登陆
     * 
     * @param bool $auto_redirect 在未登录的时候，是否自动跳转到login.sina.com.cn的登录页面
     * @return bool 是否登录
     */
    public static function is_logon($auto_redirect = 1){
        $sso_client = self::instance_sso_client();
        if (self::$is_logon === null){
            self::$is_logon = $sso_client->isLogined(intval(!$auto_redirect));
        }
        return self::$is_logon;
    }
    
    /**
     * 从cookie中获取基本用户信息，如uid, gender, screenname等
     */
    public static function get_user_info(){
        if (!self::is_logon()){
            throw new Comm_Weibo_Exception_SinaSSO("need login");
        }
        
        $sso_user_info = self::instance_sso_client()->getUserInfo();
        if (!isset($sso_user_info['uniqueid']) || !isset($sso_user_info['uid']) || !isset($sso_user_info['displayname'])){
            throw new Comm_Weibo_Exception_SinaSSO("need relogin");
        }
        
        if (isset($sso_user_info['uniqueid'])){
            $sso_user_info['uid'] = $sso_user_info['uniqueid'];
        }
        
        return $sso_user_info;
    }
    
    /**
     * 销毁cookie
     */
    public static function logout(){
        $sso_client = self::instance_sso_client();
        $sso_client->logout();
    }
    
    /**
     * 确保sso只被实例化一次
     */
    private static function instance_sso_client(){
        if (self::$sso_client === null){
            self::$sso_client = new SSOClient();
        }
        
        return Comm_Weibo_SinaSSO::$sso_client;
    }
}
