<?php
/**
 * 初始化viewer、owner信息 
 * 	TODO 目前订单系统内嵌于其他页面
 *     	因而精简化这个  < 权限验证 > plugin
 *     
 * @author  	    liuyu6
 * @version     	2013-03-14
 * @copyright  	copyright(2013) weibo.com all rights reserved
 *
 */
class Plugin_Authorize extends Swift_Plugin {
    public $viewer_info = NULL;
    public $viewer_uid = NULL;
    public $must_check_session = FALSE;  //废弃了
    public $viewer_require = true;
    
    public function run() {
        $authorize = Swift_Dispatcher::$controller_obj->authorize;
        if(Controller::NOT_LOGIN === $authorize || Controller::MAYBE_LOGIN === $authorize){
            $this->viewer_require = false;
        }else{
            $this->viewer_require = true;
        }

        //关键操作要验证用户的session
        $check_session = Swift_Dispatcher::$controller_obj->check_sesson;
        if($check_session == Controller::MUST_CHECK_SESSON){
            $this->must_check_session = TRUE;
        }else{
            $this->must_check_session = FALSE;
        }

        $this->init_viewer();
        $this->init_owner();
    }

    public function init_viewer() {
        try {
            $from = Comm_Context::param('scfrom');
            $signed_request = $_POST["signed_request"];
            $ouidparam = Comm_Context::param('ouid','');
            if($signed_request)
            {
                Comm_Context::set('appsinfo',$signed_request);
            }
            else
            { 
                if(!$ouidparam)
                {
                    $referer = $_SERVER['HTTP_REFERER'];
                    $refer_ouid = str_replace("http://apps.weibo.com/","",$referer);
                    $refer_array = explode("/",$refer_ouid);
                    $ouid        = $refer_array[0];
                    Comm_Context::set('ouid_ios',$ouid);
                    Comm_Context::set('ouid',$ouid);
                }
            }
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            if (strstr($userAgent, "Alipay")) {
        	    Comm_Context::set('alipaylogin', 1);
            }
            try{
                Comm_Context::set('cookie_sue_sup', 1);
//                $sso_info = Comm_Weibo_SinaSSO::get_user_info($this->must_check_session);
                $sso_info = Comm_Weibo_SinaSSO2::get_user_info();
            }catch(Exception $e) {}

            $come_from_mobile = Tool_Mobile::come_from_mobile();
            
//            if(!$sso_info && Comm_Context::cookie('SUW')){
//                // TODO
//                $come_from_mobile = Tool_Mobile::come_from_mobile();
//
//                $mobile_auth = Swift_Dispatcher::$controller_obj->authorize;
//                if (Controller::NOT_LOGIN == $mobile_auth) {
//                    return ;
//                }
//
//                if($come_from_mobile == true) {
//                    $sso_info = Comm_Weibo_SinaWirelessSSO::get_user_info();
//                    Comm_Context::set('use_suw', 1); // 需要修改openapi http认证信息
//                }else {
//                    throw new Comm_Weibo_Exception_SinaSSO("need relogin");
//                }
//            }
            if (!$sso_info['uid']) {
                if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'alipay') !== false){
                    if(!empty($_SERVER['QUERY_STRING'])){
                        $backUrl = urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']."?". $_SERVER['QUERY_STRING']);
                    }else{
                        $backUrl = urlencode("http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
                    }
                    //header('Location: https://passport.sina.cn/signin/signin?entry=mweibo&r=' . $backUrl);
                    header('Location: https://passport.weibo.cn/signin/login?entry=mweibo&hff=1&zhifu=1&r=' . $backUrl);
                }
                throw new Comm_Weibo_Exception_SinaSSO("need relogin");
            }
        } catch (Comm_Weibo_Exception_SinaSSO $e) {           
            if ($this->viewer_require) {
                if(isset($come_from_mobile) && ($come_from_mobile == true)) {
                    Tool_Redirect::user_not_login_h5();
                }else {
                    Tool_Redirect::user_not_login();
                }    
            } else {
                return;
            }
        }catch (Exception $e) {
            Tool_Log::fatal($e->getMessage());
            Tool_Redirect::page_not_found();
            return;
        }

        $this->viewer_uid = $sso_info['uid']; //登录者uid

        try {
            $this->viewer_info = Dr_User::get_user_info($this->viewer_uid);
        } catch (Comm_Exception_Program $e) {
            // do something
            if($this->viewer_require) {
                Tool_Redirect::unlogin();
            } else {
                return;
            }
        }
        Comm_Context::set('viewer', $this->viewer_info);
    }

    public function init_owner() {
        $owner_uid = Comm_Context::param('uid', 0);
        /*ugly hack for PC DP page allow user visit
          some moudle need a default user id
          fangyu1@staff.sina.com.cn 
          2013-12-5
         */
        if (0 === strcmp("0", $owner_uid)) {
            $owner_uid = 0;
        }
        $owner_domain = Comm_Context::param('domain');
        $owner_nick = Comm_Context::param('nick');
        $viewer = Comm_Context::get('viewer', FALSE);

        //传递的uid或domain为登录用户的时，owner即为viewer
        if (FALSE !== $viewer) {
            if ($viewer->id == $owner_uid || $viewer->domain == $owner_domain) {
                Comm_Context::set('owner', $viewer);
                return;
            }
        }
        //以domain取owner
        if ($owner_domain !== NULL) {
            try {
                $owner = Dr_User::get_user_info_by_domain($owner_domain);
                Comm_Context::set('owner', $owner);
                return;
            } catch (Comm_Exception_Program $e) {
                $this->deal_user_exp($e);
            }
        }

        //以uid取owner
        if (0 !== $owner_uid) {
            try {

                $owner_info = Dr_User::get_user_info($owner_uid);
                Comm_Context::set('owner', $owner_info);
                return;
            } catch (Comm_Exception_Program $e) {
                try {
                    $owner = Dr_User::get_user_info_by_domain($owner_uid);
                    Comm_Context::set('owner', $owner);
                    return;
                }catch (Comm_Exception_Program $e) {
                    $this->deal_user_exp($e);
                }
            }
        }

        //以昵称取owner
        if(NULL != $owner_nick){
            try {
                $owner = Dr_User::get_user_info_by_screen_name($owner_nick);
                Comm_Context::set('owner', $owner);
                return;
            } catch (Comm_Exception_Program $e) {
                $search_url = Comm_Util::conf('domain.search').'/user/'.urlencode($owner_nick).'&Refer=at';
                header('Location:'.$search_url); 
                exit;
            }
        }

        //未传递uid和domain时，owner等于viewer
        Comm_Context::set('owner', $viewer); 
    }
    /**
     * 处理用户信息获取异常
     * @param Exception $e
     */
    private function deal_user_exp(Exception $e) {
        Tool_Redirect::user_not_exists();
        return TRUE;
    }

    public function is_cli_enable() {
        return false;
    }
}

