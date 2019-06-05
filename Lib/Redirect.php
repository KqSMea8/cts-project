<?php

class Lib_Redirect {

    public static function pageNotFound() {
        $url = "https://weibo.com/sorry?pagenotfound";
	    header("Location: $url");
	    exit;
    }
	
    public static function login($back_url='') {
        if($back_url){
            $back_url = 'https://'.$_SERVER['HTTP_HOST'] .'/'.$back_url;
        }else{
            $back_url = 'https://'.$_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
            $back_url_arr = parse_url($back_url);
            $params = $back_url_arr['query'];
            $back_url = Comm_Util::conf("Env.domain") . '?' . $params;

        }

        $data = Lib_Config::i18n("common.need_login");
        
        if(stristr($_SERVER['HTTP_USER_AGENT'],'alipay')){
            $url = "https://passport.weibo.cn/signin/alogin?entry=fans_dig&r=".urlencode($back_url);
        }else{
            $url = "https://passport.weibo.cn/signin/welcome?entry=mweibo&r=".urlencode($back_url);
        }

        $data['data'] = array('url' => $url);
        echo json_encode($data);
	    exit;
    }
    public static function loginH5() {
        $back_url = 'https://'.$_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
        $back_url_arr = parse_url($back_url);
        $params = $back_url_arr['query'];
        $back_url = Comm_Util::conf("Env.domain") . '?' . $params;

        $url = "https://weibo.com/login.php?url=$back_url";
        //$url = 'https://passport.weibo.cn/signin/login?entry=mweibo&r=' . $back_url;
        if(stristr($_SERVER['HTTP_USER_AGENT'],'alipay')){
            $url = "https://passport.weibo.cn/signin/alogin?entry=fans_dig&r=".urlencode($back_url);
        }else{
            $url = "https://passport.weibo.cn/signin/welcome?entry=mweibo&r=".urlencode($back_url);
        }
        header("Location: $url");
	    exit;
    }
}
