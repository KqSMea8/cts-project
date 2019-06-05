<?php
/**
 *  重要： 由于动态平台已经内置SSOClient包，所以如下require进来的文件仅当非动态平台环境时可用。
 *   如需要修改，请确认您对SSOClient的用法明晰，并自行对修改负责。
 *   
 *    其他情况，请自行加载 SSOConfig
 * @author  	liuyu6
 * @version 	2013-04-07
 * @copyright  	copyright(2013) weibo.com all rights reserved
 *
 */
if(!class_exists('SSOWirelessClient', false)){
	require_once T3PPATH.'/sinasso/SSOWirelessClient.php';
}
 
class Comm_Weibo_SinaWirelessSSO{
    /**
     * @var object SSOWirelessClient 实例
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
            self::$is_logon = $sso_client->is_logined();
        }
        return self::$is_logon;
    }
    
    /**
     * 从cookie中获取基本用户信息，如uid, gender, screenname等
     */
    public static function get_user_info()
    {
    	if (!self::is_logon()) 
    	{
        	$url  = self::get_login_url();
        	$uri  = $_SERVER['REQUEST_URI'];
        	$from = "";
        	$ua   = $_SERVER['HTTP_USER_AGENT'];
        	if (strstr($ua, "Alipay")) 
        	{
        	    $from = "alipay";
        	}
        	//支付宝app扫码进入dp页流程
        	if((strstr($uri,"/h5/seller/product/detail") && $from == "alipay") || strstr($uri,"/h5/seller/product/aliblank"))
        	{
        	    //TODO
        	    if (strstr($uri,"/h5/seller/product/aliblank"))
        	    {
                    return;   
        	    }
        	    $uriArr = parse_url($uri);
        	    $params = $uriArr['query'];
        	    header("Location: http://mall.sc.weibo.com/h5/seller/product/aliblank?".$params);
        	    
        	    return;
        	}
            //  不支持cookie，所以跳转到登陆页
            if($url === false) {
                $url = 'http://weibo.cn';
            }
            setcookie('loginuid', 1, time() + 1000, '/');
            
            if (!strstr($uri, "h5/seller/product/detail") && 
	            !strstr($uri, "h5/temp/nosupport") && 
	            !stristr($uri, "h5/products") && 
	            !stristr($uri, "aj/like") &&
				!stristr($uri, "h5/redenvelope")
				) 
            {
            	header("Location: $url");
                exit();
            }   
        }
        parse_str(urldecode(Comm_Context::cookie('SUW')), $sso_info);
        return $sso_info;
    }

    private static function get_login_url() {
        //获取跳转地址      
//             //获取跳转地址,需要登陆的时候,会跳转到微博h5登陆页面
//             $url = SSOWirelessClient::get_login_url('h5');
//             //设置跳转成功后,回跳地址,不设置为当前页面
//             SSOWirelessClient::set_back_url('https://game.weibo.cn/');
//             $url = SSOWirelessClient::get_login_url();
        return SSOWirelessClient::get_login_url();
    }
    /**
     * 确保sso只被实例化一次
     */
    private static function instance_sso_client(){
        if (self::$sso_client === null){
            self::$sso_client = new SSOWirelessClient();
        }
        
        return Comm_Weibo_SinaWirelessSSO::$sso_client;
    }
}