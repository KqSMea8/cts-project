<?php

class Tool_Redirect {

	public static $page_not_found_url = 'http://weibo.com/sorry?pagenotfound';
	public static $user_not_exists_url = 'http://weibo.com/sorry?usernotexists';
	public static $user_not_login_url = 'http://weibo.com/login.php';
    public static $gift_not_found_url = 'http://mall.sc.weibo.com/gift/sorry';
	public static $h5_user_not_login_url = 'http://m.weibo.cn/login';
	public static $h5_page_not_found_url = 'http://weibo.cn';
	public static $page_order_error_url = 'http://mall.sc.weibo.com/order/failed';
	public static $order_not_match_owner = 'http://mall.sc.weibo.com/order/list';
    public static $h5_temp_limitation_url = 'http://mall.sc.weibo.com/h5/temp/limitation';
    public static $h5_temp_error_url = 'http://mall.sc.weibo.com/h5/temp/error';
	
    public static $error_page = array(
        'pagenotfound' => 'http://weibo.com/sorry?pagenotfound',
        'usernotexists' => 'http://weibo.com/sorry?usernotexists',    
        'orderfail' => 'http://mall.sc.weibo.com/failed?orderfail',
        'sellcenterfail' => 'http://mall.sc.weibo.com/failed?sellcenterfail',
        'iidfail' => 'http://mall.sc.weibo.com/failed?iidfail',
        'iteminfofail' => 'http://mall.sc.weibo.com/failed?iteminfofail',
    	'invalidfirm'  => 'http://mall.sc.weibo.com/failed?invalidfirm',
        'commodityinfofail' => 'http://mall.sc.weibo.com/failed?commodityinfofail',
        'statusfail' => 'http://mall.sc.weibo.com/failed?statusfail',
        'vieweridfail' => 'http://mall.sc.weibo.com/failed?vieweridfail',
        'firmidfail' => 'http://mall.sc.weibo.com/failed?firmidfail',
        'default' => 'http://mall.sc.weibo.com/failed',
	    'failed' => 'http://mall.sc.weibo.com/failed',
	    'refuse' => 'http://mall.sc.weibo.com/refuse',
    	'sellout'=> 'http://mall.sc.weibo.com/failed?sellout',
	    'redenvelope_h5' => 'http://mall.sc.weibo.com/h5/redenvelope/failed',
    	'account_illegal' => 'http://mall.sc.weibo.com/redenvelope/failed?type=account_illegal',
    	'invalid_client' => 'http://mall.sc.weibo.com/h5/redenvelope/failed?type=invalid_client',
    	'testing'=> 'http://mall.sc.weibo.com/redenvelope/failed?type=testing',
    	'invalid_access' => 'http://mall.sc.weibo.com/redenvelope/failed?type=invalid_access&sinainternalbrowser=topnav',
	);
	
	
	public static function response($url, $code = 0, $msg = '') {
		if (Comm_Context::is_xmlhttprequest()) {
			if (Comm_Context::param('ajaxpagelet', 0)) {
				echo "parent.windows.location = $url";
				exit;
			} else {
				Tool_Jout::normal($code, $msg, $url);
				exit;
			}
		} else {
			if ($_SERVER['SCRIPT_URL'] != '/sorry') {
				header("Location: $url");
				exit;
			}
		}
	}

	public static function only_location($url, $code = 0, $msg = '') {
	    if ($_SERVER['SCRIPT_URL'] != '/sorry') {
	        header("Location: $url");
	        exit;
	    }
	}
	
	public static function h5_page_not_found() {
	    self::response(self::get_h5_page_not_found_url());
	}
	
	public static function page_not_found($type = 'pagenotfound') {
    
      if(isset(self::$error_page[$type]))
         $url = self::$error_page[$type];
      else
         $url = self::$error_page['pagenotfound'];
        self::response($url);
	}

    public static function gift_not_found() {
        self::response(self::get_gift_not_found_url());
    }

	public static function user_not_exists() {
		self::response(self::get_user_not_exists_url());
	}

	public static function user_not_login() {
        if(!empty($_SERVER['QUERY_STRING'])){
		self::response(self::get_user_not_login_url()."?url=". urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']."?".$_SERVER['QUERY_STRING']) );
        }else{
            self::response(self::get_user_not_login_url()."?url=". urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']));
        }
	}

	public static function user_redirect_after_login($url)
	{
		self::response(self::get_user_not_login_url() . '?url=' . $url) ;
	}

	public static function get_page_not_found_url() {
		return self::$page_not_found_url;
	}

	public static function get_order_not_match_owner_url() {
	    self::response(self::$order_not_match_owner);
	}
	
    public static function get_gift_not_found_url() {
        return self::$gift_not_found_url;
    }

	public static function get_user_not_exists_url() {
		return self::$user_not_exists_url;
	}

	public static function get_user_not_login_url() {
		return self::$user_not_login_url;
	}
	
	public static function user_not_login_h5() {
	    self::response(self::get_user_not_login_url_h5());
	}
	
	public static function get_user_not_login_url_h5() {
	    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'alipay') !== false){
	        if(!empty($_SERVER['QUERY_STRING'])){
	            $backUrl = urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']."?". $_SERVER['QUERY_STRING']);
	        }else{
	            $backUrl = urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
	        }
	        //return 'https://passport.sina.cn/signin/signin?entry=mweibo&r=' . $backUrl;
	        return 'https://passport.weibo.cn/signin/login?entry=mweibo&hff=1&zhifu=1&r=' . $backUrl;
	    }

	    if(!empty($_SERVER['QUERY_STRING'])){
	        return 'https://passport.weibo.cn/signin/login?entry=mweibo&r='. urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']."?". $_SERVER['QUERY_STRING']);
	    }else{
	        return 'https://passport.weibo.cn/signin/login?entry=mweibo&r='. urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
	    }
	}
	
	public static function get_h5_page_not_found_url() {
	    return self::$h5_page_not_found_url;
	}

    public static function get_h5_temp_limitation_url($msg)
    {
       self::response(self::$h5_temp_limitation_url . '?msg=' . $msg);
    }

    public static function get_h5_temp_transfer_url($msg)
    {
       self::response(self::$h5_temp_error_url . '?msg=' . $msg);
    }

    //�û�������PC�鿴ת�˵��б�
    public static function get_refuse_url($msg)
    {
       self::response(self::$error_page['refuse'] . '?msg=' . $msg);
    }
    
    public static function get_specify_url($url) {
    	self::response($url);
    }
    
    public static function h5_not_login($back_url) {
    	$uri = 'https://passport.weibo.cn/signin/login?entry=mweibo&hff=1&zhifu=1&r=' ;
    	$url = $uri . urlencode($back_url) ;
    	return $url ;
    }
}
