<?php
/**
 * 调用pay下面的接口
 * @copyright  copyright(2014) weibo.com all rights reserved
 * @author     mengyu5
 * @version    2016-01-12
 */

class Comm_Weibo_Pay {
    const DOMAIN_URL = 'http://pay.sc.weibo.com';
	const API_KEY = 'c56f4ab4ea062030c66ec';
	
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

	//获取微博支付余额
	public function get_user_account($data)
	{
		$data['sign_type'] = 'md5';
		$url = self::DOMAIN_URL . '/api/comm/user/avail';
		return $this->get_get_result($url, self::API_KEY, $data);
	}
}
