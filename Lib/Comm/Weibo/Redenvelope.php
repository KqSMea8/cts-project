<?php
/**
 * Comm_Weibo_Redenvelope 
 * 过年红包项目接口
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-11-11
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Comm_Weibo_Redenvelope
{
    const API_KEY = 'c935202338f9543576ac' ;//'f4793789d78e66793ca3';
    const DOMAIN = 'http://api.sc.weibo.com';
    const API_DOMAIN = 'http://api.sc.weibo.com/v2/bonus/p' ;
    //const CREATE_PAY_URL = 'http://api.sc.weibo.com/v2/bonus/p/pay';
    const CREATE_PAY_URL = 'http://weibo.cn/weibobrowser/payment/order?order_type=bonus&path=';
    //const CREATE_PAY_URL1 = 'http://10.75.15.136:8080/v2/bonus/p/pay';
    const CREATE_PAY_URL1 = 'http://api.sc.weibo.com/v2/bonus/p/pay';
    //const SPREAD_URL = 'http://10.75.15.136:8080/v2/bonus/p/spread';
    const SPREAD_URL = 'http://api.sc.weibo.com/v2/bonus/p/spread';
    const RECV_URL = 'http://api.sc.weibo.com/v2/bonus/p/recv' ;
    const SET_INFO = 'http://api.sc.weibo.com/v2/bonus/p/query/set/info' ;
    const RECV_LIST_BYSET = 'http://api.sc.weibo.com/v2/bonus/p/query/set/recvlist' ;
    const RECV_LIST_USER = 'http://api.sc.weibo.com/v2/bonus/p/query/recvlist' ;
    const QUERY_SET_AUTH = 'http://api.sc.weibo.com/v2/bonus/p/query/set/auth' ;
    const BONUS_INFO = 'http://api.sc.weibo.com/v2/bonus/p/query/bonusinfo';
    const CSET_INFO = 'http://api.sc.weibo.com/v2/bonus/c/set/querymall';

    public function get_post_result($url, $platform_key, $data = array()) {
        try
        {
            $request = new Comm_HttpRequest($url);
            $request->set_method('POST');
            foreach($data as $key => $val)
            {
                $request->add_post_field($key, $val);
            }
            
            $sign = Tool_Sign::generate_sign($data, $platform_key);
            $request->add_post_field('sign', $sign);

            $res = $request->send();
            $result = json_decode($request->get_response_content(), true) ;
            return $result;
        } catch(Exception $e)
        {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    public function get_get_result($url, $platform_key, $data = array()){
        try{
            $request = new Comm_HttpRequest($url);
            $request->set_method('GET');
            foreach($data as $key => $val)
            {
                $request->add_query_field($key, $val);
            }

            $sign = Tool_Sign::generate_sign($data, $platform_key);
            $request->add_query_field('sign', $sign);
            $res = $request->send();
            return $request->get_response_content();
        } catch(Exception $e)
        {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    /**
     * createPay 
     * 创建或支付一个红包
     * @param mixed $data 
     * @return void
     */
    public function createPay($data){
        $url = self::CREATE_PAY_URL . urlencode(self::CREATE_PAY_URL1);
        $key = self::API_KEY;
    	$data['sign_type'] = 'md5';
        $data['sign'] = Tool_Sign::generate_sign($data, $key);
        $params = http_build_query($data);
        $url = $url . '&' . $params;
        return array('code' => 100000, 'data' => array('url' => $url));
        //return $this->get_post_result($url, $key, $data);
    }
    
    public function spread($data){
        $url = self::SPREAD_URL;
        $key = self::API_KEY;
    	$data['sign_type'] = 'md5' ;
        return $this->get_post_result($url, $key, $data);
    }

    /**
     * receive
     * 领取一个红包
     * @param mixed $data
     * @return void
     */
    public static function recvMoney($data){
    	$url = self::RECV_URL;
    	$key = self::API_KEY;
    	$data['sign_type'] = 'md5' ;
    	return self::get_post_result($url, $key, $data);
    }
    /**
     * getsetInfo
     * 获取一个红包集的id
     * @param mixed $data
     * @return void
     */
    public static function get_set_info($data) {
    	$data['sign_type'] = 'md5' ;
    	return self::get_post_result(self::SET_INFO, self::API_KEY, $data) ;	
    }
    
    /**
     * getrecvlis
     * 获取某红包被领取的列表
     * @param mixed $data
     * @return void
     */
    public static function get_recv_list_bysetid($data) {
    	$data['sign_type'] = 'md5' ;
    	return self::get_post_result(self::RECV_LIST_BYSET, self::API_KEY, $data) ;
    }
    
    /**
     * getrecvlist
     * 获取某人领取的红包信息
     * @param mixed $data
     * @return void
     */
    public static function get_recv_list_byuid($data) {
    	$data['sign_type'] = 'md5' ;
    	
    	return self::get_post_result(self::RECV_LIST_USER, self::API_KEY, $data) ;
    }
    
    /**
     * getsendlist
     * 获取某人发出的红包列表
     * @param mixed $data
     * @return void
     */
    public static function get_send_list_byuid($data) {
    	$data['sign_type'] = 'md5' ;
    	$url = self::API_DOMAIN . '/query/set/list' ;
    	return self::get_post_result($url, self::API_KEY, $data) ;
    }
    
    /**
     * query_ali_account
     * 查询用户绑定的支付宝账号
     * @param mixed $data
     * @return void
     */
    public static function query_ali_account($data) {
    	$data['sign_type'] = 'md5' ;
    	$url = self::API_DOMAIN . '/aliaccount' ;
    	return self::get_post_result($url, self::API_KEY, $data) ;
    }
    
    /**
     * casher
     * 提现接口
     * @param mixed $data
     * @return void
     */
    public static function casher($data) {
    	$data['sign_type'] = 'md5' ;
    	$url = self::API_DOMAIN . '/casher' ;
    	return self::get_post_result($url, self::API_KEY, $data) ;
    }
    
    /**
     * 某人领取红包的金额查询
     * @param mixed $data
     * @return void
     */
    public static function amount_of_set($data) {
    	$data['sign_type'] = 'md5' ;
    	$url = self::API_DOMAIN . '/query/amount' ;
    	return self::get_post_result($url, self::API_KEY, $data) ;
    }

    /**
     * 查询红包金额排名接口
     * @param mixed $data
     * @return void
     */
    public static function query_rank($data) {
    	$data['sign_type'] = 'md5' ;
    	$url = self::API_DOMAIN . '/query/rank' ;
    	return self::get_post_result($url, self::API_KEY, $data) ;
    }

    /**
     * @param $data
     * @return bool|mixed
     *查询用户$uid是否有查询该群组红包集$set_id详情的权限
     */
    public static function get_query_set_auth($data) {
        $data['sign_type'] = 'md5' ;
        return self::get_post_result(self::QUERY_SET_AUTH, self::API_KEY, $data) ;
    }
    
    public static function set_comment($data) {
    	$data['sign_type'] = 'md5' ;
    	$url = self::API_DOMAIN . '/query/set/comment' ;
    	
    	return self::get_post_result($url, self::API_KEY, $data) ;
    }

    /**
     * receive
     * 根据红包bonus_id查询红包信息
     * @param mixed $data
     * @return void
     */
    public static function get_bonus_info_by_id($data){
        $url = self::BONUS_INFO;
        $key = self::API_KEY;
        $data['sign_type'] = 'md5' ;
        return self::get_post_result($url, $key, $data);
    }

    public static function get_cset_info_by_setid($data){
        $url = self::CSET_INFO;
        $key = self::API_KEY;
        $data['sign_type'] = 'md5' ;
        return self::get_post_result($url, $key, $data);
    }

    /**
     * getrecvlist
     * 获取某人领取的红包信息
     * @param mixed $data
     * @return void
     */
    public static function get_recv_confirm_byuid($data) {
        $data['sign_type'] = 'md5' ;
        $url = self::API_DOMAIN . '/query/recvcash' ;
        return self::get_post_result($url, self::API_KEY, $data) ;
    }

    /**
     * checkPayState 
     * 验证用户是否还能创建收款单
     * @param mixed $data 
     * @static
     * @access public
     * @return void
     */
    public static function checkPayState($data) {
        $data['sign_type'] = 'md5';
        $url = self::DOMAIN . '/bonus/p/paycheck' ;
        return self::get_post_result($url, self::API_KEY, $data) ;
    }

    public function firmSend($data){
        $data['sign_type'] = 'md5';
        $url = self::DOMAIN . '/bonus/c/create' ;
        return self::get_post_result($url, self::API_KEY, $data) ;
    }
    
    public function firmSendNewApi($data, $key){
        $data['sign_type'] = 'md5';
        $url = 'http://hb.e.weibo.com/v2/bonus/c/create' ;
        return self::get_post_result($url, $key, $data) ;
    }

    public static function get_count($data){
        $data['sign_type'] = 'md5';
        //$url = 'http://10.73.15.236/v2/bonus/p/query/quantity' ;
		$url = 'http://i.hb.e.weibo.com/v2/bonus/p/query/quantity';
        return self::get_post_result($url, self::API_KEY, $data) ;
    }
}
