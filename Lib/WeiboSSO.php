<?php

/*
 * 
 * SSO SDK 二次封装
 * 
 * SSO SDK文档:  http://wiki.intra.sina.com.cn/pages/viewpage.action?pageId=24117637
 */

//从动态平台的公共include_path加载SDK
//include_once "sso/sdk/client/0.6.22/client.php";  

//从本地加载SDK
require_once PATH_THIRD_LIB.'/sso_sdk_client-0.6.22/client.php';  
require_once PATH_THIRD_LIB.'/sso_sdk_client-0.6.22/config.php'; 
require_once PATH_THIRD_LIB.'/sso_sdk_client-0.6.22/user.php'; 



class Lib_WeiboSSO{

    
    private static $uid = null;
    

    
    /**
     * 获取用户UID,  未登录返回false或者跳到自动登录
     * 
     * @param bool $auto_redirect 在未登录的时候，是否自动跳转到login.sina.com.cn的登录页面
     * @return bool 是否登录
     */
    public static function getUID($auto_redirect = 1){

        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $ua = strtolower($ua);
        $h5_url = 0;
        if(strstr($ua, 'weibo')|| strstr($ua, 'micromessenger')) {
            $h5_url  = 1;
        }
        if (self::$uid === null){
            self::init();
            try{
                $user = Sso_Sdk_Client::instance()->get_user();
                if ($user->is_status_normal()) {
                    self::$uid = $user->get_uid();
                } else {
                    if($auto_redirect) {
                        if($h5_url){
                            header("Location: http://m.weibo.cn/login?url=". urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']."?".$_SERVER['QUERY_STRING']) );
                            // header("Location: https://passport.weibo.cn/signin/login?entry=mweibo&hff=1&zhifu=1&r= ". urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']."?".$_SERVER['QUERY_STRING']));
                        }else{
                            if(!empty($_SERVER['QUERY_STRING'])){
                                header("Location: https://weibo.com/login.php?url=". urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']."?".$_SERVER['QUERY_STRING']) );
                            }else{
                                header("Location: https://weibo.com/login.php?url=". urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']));
                            }
                        }

                        exit;
                    }
                    self::$uid = false;
                }
            } catch (Exception $e) {
                throw $e; //暂时交给外面处理
            }
        }
        return self::$uid;
    }

    private static function init() {
        
        //具体设置项参考SSO文档
        Sso_Sdk_Config::set_user_config(array(
                'service'   => '',
                'entry'     => Lib_Config::get('sso.entry'),
                'pin'       => Lib_Config::get('sso.pin'),
                'domain'    => '.weibo.com', 
                'idc'       => 'yf', 
                'ignore_verify_flag'       => array("all"), 
                'check_domain'       => false, 
                'autologin'  => false, 

        ));
    }
}
