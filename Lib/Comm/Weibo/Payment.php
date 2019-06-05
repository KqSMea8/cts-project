<?php

class Comm_Weibo_Payment
{
    const CREATE_PAY_URL = 'http://api.sc.weibo.com/v2/pay/create';
	const ACCOUNT_QUERY_URL = 'http://api.sc.weibo.com/v2/pay/accountquery';
    const CHECK_PAIEDUSER = 'http://api.sc.weibo.com/v2/pay/paieduser';
    const CHECK_PAIEDUSER2 = 'http://api.sc.weibo.com/v2/pay/pay/firstpay' ;
    /**
     * 获取GET请求的响应内容
     * 
     * @param string $url
     * @param BOOL $is_raw_url 是否直接使用原始url，此参数解决GET参数传递数组的情况，如id[]=xxx&id[]=yyy
     * @return mixed
     */
    public function get_response_result($url, $platform_key, $data = array(), $flag = false) {
        try
        {
            $request = new Comm_HttpRequest();
            $request->url = $url;
            foreach($data as $key => $val)
            {
                $request->add_post_field($key, $val);
            }
            if (!$flag) 
            {
            	unset($data['uid']);  //uid不参与加密;
            }
            
            $sign = Tool_Sign::generate_sign($data, $platform_key);
            $request->add_post_field('sign', $sign);
            $ren= $request->send();
            return $request->get_response_content();
        } catch(Exception $e)
        {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    public function create_pay($data, $key)
    {
        $res = $this->get_response_result(self::CREATE_PAY_URL, $key, $data);
        return $res;
    }
    
    public function query_account($data, $key)
    {
    	$res = $this->get_response_result(self::ACCOUNT_QUERY_URL, $key, $data, TRUE) ;
    	return json_decode($res, true) ;
    }

    /*
     * 检查用户是否用过微博支付
     */
    public function check_paieduser($data, $key){
    	$res = $this->get_response_result(self::CHECK_PAIEDUSER, $key, $data, TRUE);
    	return json_decode($res, true);
    }
    
    public function check_paieduser_by_tradeid($data, $key) {
    	$res = $this->get_response_result(self::CHECK_PAIEDUSER2, $key, $data, TRUE) ;
    	return json_decode($res, true) ;
    }
}
