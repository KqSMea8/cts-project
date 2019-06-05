<?php

class Tool_Mobile {

	public static function come_from_mobile() {
		$user_agent_info = Comm_Clientprober::get_client_agent();
		return isset($user_agent_info['mobilephone']) && $user_agent_info['mobilephone'];
	}

    public static function gotoH5(){
        $post_string = http_build_query($_POST);
        if (empty($_SERVER['QUERY_STRING'])){
           header('location: http://'.$_SERVER["HTTP_HOST"].'/h5'.$_SERVER['REQUEST_URI'].'?'. $post_string);
        }else{
            header('location: http://'.$_SERVER["HTTP_HOST"].'/h5'.$_SERVER['REQUEST_URI'].'?'.$_SERVER['QUERY_STRING'] . '&' . $post_string);
        }
        exit;
    }
    
    /**
     * TODO 2012-12-4 15:05:26 : 由于是 e.weibo.cn跳转，gsid一次有效，
     * 	  故而为了避免 e.weibo.cn 中 authorize-plugin 插件预先解析gsid，
     *    e.weibo.cn 会将 无线转至其的请求rewrite 至 hd.e.weibo.com
     *    
     * 2013-5-17  互动没用cookie，必须要有fsid
     * @return Ambigous <>|unknown|boolean
     */
    public static function get_fsid() {
        $fsid = Comm_Context::param('gsid', false);
        if($fsid) {
            return $fsid;
        }
    
        $fsid = Comm_Context::param('fsid', false);
        if($fsid) {
            return $fsid;
        }
        
        // 考虑其他情况取fsid
        $fsid = Comm_Context::form('fsid', false);
        if($fsid) {
            return $fsid;
        }
    
        // TODO 以下方式目前无效，暂且保留
        // 和企业微博沟通后，会在header里面种入fsid,故而可以直接在这里跳转
        if(isset($_SERVER['HTTP_REFERER'])) {
            $headers = @get_headers($_SERVER['HTTP_REFERER']);
            if(isset($headers['Fsid2Hudong'])) {
                return $headers['Fsid2Hudong'];
            }
    
            // 从 referer 里面取，先取fsid，再取gsid(兼容企业微博电子会员取fsid做法)
            parse_str($_SERVER['HTTP_REFERER'], $refer_var);
            if(isset($refer_var['fsid'])) {
                return $refer_var['fsid'];
            }elseif (isset($refer_var['gsid'])) {
                return $refer_var['gsid'];
            }
        }
    
        // 再从cookie里面取gsid(兼容企业微博电子会员取fsid做法)
        if (isset($_COOKIE['gsid_CTandWM'])) {
            return $_COOKIE['gsid_CTandWM'];
        }
    
        return false;
    }
    
    public static function get_hudong_h5_url() {
        // 互动H5没有用cookie，需要保存
        $hd_h5_url = '';
        $fsid = self::get_fsid();
        $ext = Comm_Context::param('ext', ''); // like actid_3548
        if($ext == '') {
            $ext = Comm_Context::form('ext', '');
        }
        if(!empty($ext) && strpos($ext, '_') !== false) {
            $actid = (int)(substr($ext, (strpos('actid_123','_')+1)));
            $hd_h5_url = Comm_Config::get('domain.hudong') . '/h5/selldetail?actid=' . $actid . '&fsid=' . $fsid;
        }
        
        return $hd_h5_url;	 
    }

    //转账身份验证
    public static function check_transfer(){
        $viewer = Comm_Context::get('viewer', false);
        $check_alipay = 'alipay';
        $check_weibo = 'weibo';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; 
        $ua = strtolower($ua);
        if(!$ua || (!strstr($ua, $check_weibo) && !strstr($ua, $check_alipay))){
            $no_support_page =  Comm_Config::get('domain.ordersc') . '/h5/temp/nosupport';
            header("Location: " . $no_support_page);
            exit;
        }

        if (!$viewer){
            Tool_Redirect::user_not_login_h5();
        }

        //蓝V用户不能转账
        if ($viewer->verified_type > 0 && $viewer->verified_type < 8){ 
            Tool_Redirect::get_h5_temp_transfer_url('transferv');
            return;
        }   

        /*
        //不在白名单里的用户不能转账
        $uids = Comm_Config::get('transfer_uids.uids');
        if (!in_array($viewer->id, $uids)){
            Tool_Redirect::get_h5_temp_transfer_url('transfer_forbiden');
            return;
        }
        */

        //开通微博支付的商家不能转账
        $uInfo = array('merchant_id' => $viewer->id);
        $res = Dr_Firm_Merchantinfo::check_merchant_exist($uInfo);
        if (!$res){//接口错误
             Tool_Redirect::get_h5_temp_transfer_url('接口无响应，请稍后再试！');
        }else{
            if ($res['error_code'] == 100000){
                if($res['existed']){
                    Tool_Redirect::get_h5_temp_transfer_url("微博支付收单商户,无法使用转账功能！");
                }
            }else{
             Tool_Redirect::get_h5_temp_transfer_url('接口异常');
            }
        }
    }

    /**
     * checkWeiboClient 
     * 检查是否用客户端，不是则跳到不支持页面
     * @static
     * @return void
     */
    public static function checkWeiboClient(){
        $check_alipay = 'alipay';
        $check_weibo = 'weibo';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; 
        $ua = strtolower($ua);
        if(!$ua || (!strstr($ua, $check_weibo) && !strstr($ua, $check_alipay))){
            $no_support_page =  Comm_Config::get('domain.ordersc') . '/h5/temp/nosupport';
            header("Location: " . $no_support_page);
            exit;
        }
    }

    /**
     * checkWeiboClient 
     * 检查是否用客户端，不是则跳到不支持页面new
     * @static
     * @return void
     */
    public static function checkWeiboClientNew(){
        $check_alipay = 'alipay' ;
        $check_weibo = 'weibo' ;
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; 
        $ua = strtolower($ua);
        if(!$ua || (!strstr($ua, $check_weibo) && !strstr($ua, $check_alipay))){
            //$no_support_page =  Comm_Config::get('domain.ordersc') . '/h5/temp/nosupport?type=new';
            $no_support_page =  Comm_Config::get('domain.ordersc') . '/h5/redenvelope/failed?type=invalid_client';
            header("Location: " . $no_support_page);
            exit;
        }

        //限制客户端5.0以上才能发红包
        /*
        $clientVersion = self::getClientVersion();
        if($clientVersion < '5'){
            //$no_support_page =  Comm_Config::get('domain.ordersc') . '/h5/temp/nosupport';
            header("Location: http://m.weibo.cn/client/version");
            exit;
        }
        */
    }

    /**
     * getClientVersion 
     * 获取客户端版本号
     * @static
     * @return void
     */
    public static function getClientVersion(){
        $clientInfo = $_SERVER['HTTP_USER_AGENT'];
        $weiboInfo = substr($clientInfo,strpos($clientInfo, 'Weibo') + 5, -1);
        $weiboInfoArr = explode( '__', $weiboInfo);
        $clientVersion = $weiboInfoArr[2];
        if (strpos($clientVersion, '_') !== false){
            $clientInfo = explode('_', $clientVersion);
            $clientVersion = $clientInfo[0];
        }
        return $clientVersion;
    }

    /**
     * checkWeiboClient 
     * 检查是否用客户端，不是则跳到不支持页面
     * 阿里手机充值页面
     * @static
     * @return void
     */
    public static function checkWeiboClientAli(){
        $check_alipay = 'alipay';
        $check_weibo = 'weibo';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; 
        $ua = strtolower($ua);
        if(!$ua || (!strstr($ua, $check_weibo) && !strstr($ua, $check_alipay))){
            $no_support_page =  Comm_Config::get('domain.ordersc') . '/h5/temp/aliweb';
            header("Location: " . $no_support_page);
            exit;
        }
    }

    /**
     * getPaymentUrl 
     * 客户端乎起支付弹窗url
     * @param mixed $params 
     * @static
     * @return void
     */
    public static function getPaymentUrl($params, $key){
        //$params['order_id'] .= '_' . rand(10000,99999);
        $cfg_follow_opt = $params['cfg_follow_opt'] ? $params['cfg_follow_opt'] : 2;
        $param_arr = array(
            'source'          => 3103002211,
            'seller_id'       => $params['seller_id'],
            'out_trade_no'    => $params['order_id'],
            'subject'         => $params['subject'],
            'body'            => $params['body'],
            'show_url'        => $params['dest_url'],//活动地址
            'notify_url'      => $params['notify_url'],//通知地址
            'return_url'      => '',
            'total_fee'       => $params['price'],
            'it_b_pay'        => '15d',
            'pay_channel'     => 100,
            'cfg_follow_opt'  => $params['cfg_follow_opt'],
        ); 
        $param_arr['sign'] = Tool_Sign::generate_sign($param_arr, $key);

        $param_arr["sign_type"] = "md5";

        $url = 'http://weibo.cn/weibobrowser/payment/order';
        $url  .= '?' . http_build_query($param_arr);
        #Tool_Log::info('call sdk url:' . $url);
        return $url;
    }
    
    public static function checkAlipayBind($returnUrl, $pid = '', $sina_source = ''){
        $version = self::getClientVersion();
        if (version_compare($version, '5.1.0') == -1){
            $bindUrl = Comm_Config::get('domain.bind_alipay');
            $backUrl = $returnUrl;
            $bindUrl = sprintf($bindUrl, $backUrl);
            header('Location: ' . $bindUrl);
            exit;
        }else{
            $sdkdata = array(
                        'sina_source'         => $sina_source,
                        'alipay_appid'        => '2013120900002307',
                        'alipay_pid'          => $pid,
                        'alipay_redirect_uri' => 'sinaweibo://',
                        'type'                => 'alipay',
                        'bindway'             => 'sdk',
                        'sina_callback_url'   => $returnUrl,
                    );
            $sdkdata = http_build_query($sdkdata);
            header('Location: http://m.weibo.cn/tb/bind?type=alipay&scheme_pre=' . urlencode('http://weibo.cn/weibobrowser/bindtaobao?sdkdata=' . urlencode($sdkdata) . '&need_call_back_url=1'));
            exit;
        }
    }

    public static function getAlipayBindUrl($returnUrl, $pid = '', $sina_source = ''){
        $version = self::getClientVersion();
        if (version_compare($version, '5.1.0') == -1){
            $bindUrl = Comm_Config::get('domain.bind_alipay');
            $backUrl = $returnUrl;
            $bindUrl = sprintf($bindUrl, $backUrl);
            return $bindUrl;
        }else{
            $sdkdata = array(
                        'sina_source'         => $sina_source,
                        'alipay_appid'        => '2013120900002307',
                        'alipay_pid'          => $pid,
                        'alipay_redirect_uri' => 'sinaweibo://',
                        'type'                => 'alipay',
                        'bindway'             => 'sdk',
                        'sina_callback_url'   => $returnUrl,
                    );
            $sdkdata = http_build_query($sdkdata);
            return 'http://m.weibo.cn/tb/bind?type=alipay&scheme_pre=' . urlencode('http://weibo.cn/weibobrowser/bindtaobao?sdkdata=' . urlencode($sdkdata) . '&need_call_back_url=1');
        }
    }

}
