<?php
/**
 * Comm_Weibo_Represent
 * 微代言接口
 * @author    chehaoman<haoman@staff.weibo.com> 
 * @created   2015/8/6
 * @copyright copyright(2015) weibo.com all rights reserved

 */
class Comm_Weibo_Represent
{
	const DOMAIN = 'http://api.sc.weibo.com';
	const API_KEY = 'fac837511ae2417cf320';
	const SIGN_TYPE = 'md5';


	public function get_post_result($url, $platform_key, $data = array()) {
        try
        {
	    	$data['sign_type'] = self::SIGN_TYPE;
            $request = new Comm_HttpRequest($url);
            $request->set_method('POST');
            foreach($data as $key => $val)
            {
                $request->add_post_field($key, $val);
				//echo "$key=$val&";
            }
            
            $sign = Tool_Sign::generate_sign($data, $platform_key);
            $request->add_post_field('sign', $sign);
			//echo "sign=$sign";
            $res = $request->send();
            return $request->get_response_content();
        } catch(Exception $e)
        {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    public function get_get_result($url, $platform_key, $data = array()){
        try{
			$data['sign_type'] = self::SIGN_TYPE;
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
     * get_task_info 
     * 查询任务信息
     * @param mixed $data 
     * @return void
     */
    public function get_task_info($data){
    	$url = self::DOMAIN . '/v2/represent/task/info';
        $key = self::API_KEY;
        return $this->get_get_result($url, $key, $data);
    }
	
	/**
     * order_authorize 
     * 订单签约
     * @param mixed $data 
     * @return void
     */
    public function order_authorize($data){
    	$url = self::DOMAIN . '/v2/represent/order/authorize';
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

	/**
     * account_sign 
     * 用户签约加入微代言
     * @param mixed $data 
     * @return void
     */
    public function account_sign($data){
    	$url = self::DOMAIN . '/v2/represent/account/sign';
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

	/**
     * account_sign 
     * 用户签约加入微代言
     * @param mixed $data 
     * @return void
     */
    public function get_weibo_info($data){
    	$url = self::DOMAIN . '/v2/represent/weibo/info';
        $key = self::API_KEY;
        return $this->get_get_result($url, $key, $data);
    }

	/**
     * get_order_info 
     * 查看订单信息
     * @param mixed $data 
     * @return void
     */
    public function get_order_info($data){
    	$url = self::DOMAIN . '/v2/represent/order/info';
        $key = self::API_KEY;
        return $this->get_get_result($url, $key, $data);
    }

	/**
     * get_user_info 
     * 查看用户信息
     * @param mixed $data 
     * @return void
     */
    public function get_user_info($data){
    	$url = self::DOMAIN . '/v2/represent/account/info';
        $key = self::API_KEY;
        return $this->get_get_result($url, $key, $data);
    }
	
	


	
	
}