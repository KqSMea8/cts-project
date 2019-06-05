<?php
/**
 * Comm_Weibo_Alipay{ 
 * 已集成的淘宝/支付宝相关接口
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-8-1
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Comm_Weibo_Alipay{

    /**
     * 根据微博uid，获取用户绑定的淘宝账号tid，
     * 若未绑定淘宝账号则tid=0
     * @param $uid
     * @return mixed
     */
    public static function getTidByUid($uid){
        try{
            $arr = array(
                    'url' => 'http://i.api.weibo.com/taccount/get.json',
                    'is_post' => false,
                    'data' => array('uid' => $uid),
            );
            $token = Tool_Curl::request($arr);
            $token = @json_decode($token, true);
            return $token;
        }catch(Exception $e) {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    public static function getTidByUidNew($uid){
        try{
			//$uid = 2820854703;
            $sign = md5(base64_encode("ret_type=all&sensitive=1&uid=$uid") . '5cae45e2d58257b3da2f67397fd6e5f2');
            $arr = array(
                    //'url' => 'http://10.210.230.36:8082/taccount/v2/get.json',
                    'url' => 'http://i2.api.weibo.com/2/taccount/v2/get.json',
                    'is_post' => true,
                    'data' => array('uid' => $uid, 'ret_type' => 'all', 'sign' => $sign,'sensitive' => 1),
            );  
            $token = Tool_Curl::request($arr);
            $token = @json_decode($token, true);
			if($token['result'] == true){
           	return $token;
			}
			return false;
        }catch(Exception $e) {
            Tool_Log::fatal($e->getMessage());
            return false;
        }   
    }


    /**
     * 根据淘宝tid，获取对应的支付宝账号
     * @param $tid
     * @return mixed
     */
    public static function get_tnickname_by_tid($tid) {
        try{
            $url = 'http://i2.api.weibo.com/2/taccount/taobao/by_tid.json';
            //请求来源，需微博主站进行分配
            $source='3865972543';
            $key = '5cae45e2d58257b3da2f67397fd6e5f2';
            $param = base64_encode("source={$source}&tid={$tid}");
            $sign = md5($param.$key);
            $data_curl = array(
                'source' => $source,
                'sign'   => $sign,
                'tid'    => $tid
            );
            $request = new Comm_HttpRequest($url);
            $request->set_method('POST');
            foreach($data_curl as $key => $val){
                $request->add_post_field($key, $val);
            }
            $request->send();
            $res = $request->get_response_content();
            return json_decode($res, true);
        }catch(Exception $e) {
            Tool_Log::fatal($e->getMessage());
            throw new Exception("接口超时，请重试！", '220005');
        }
    }

    /**
     * getMobilessoToken 
     * 商家无线免登授权接口
     * @static
     * @return void
     */
    public static function getMobilessoToken($data){
        $gateUrl = Comm_Config::get('alipay.mobilesso.gate_url');
        $rsaPath = APPPATH . Comm_Config::get('alipay.mobilesso.rsa_pri_path');
        $tokenStr = Tool_Sign::get_alipay_sign_form($data);
        $data['sign'] = Tool_Sign::get_rsa_sign($tokenStr, $rsaPath);
        $request = new Comm_HttpRequest($gateUrl);
        $request->set_method('GET');
        foreach($data as $key => $val){
            $request->add_query_field($key, $val);
//echo $key.'='.$val.'&';
        }
        $request->send();
        $res = $request->get_response_content();
        $res = json_decode($res, true);
        return $res;
    }

    /**
     * userPartnerMobilesso 
     * 商家无线免登接口
     * @static
     * @return void
     */
    public static function userPartnerMobilesso($data, $urlParams = ''){
        $url = Comm_Config::get('alipay.mobilesso.gate_url');
        $rsaPath = APPPATH . Comm_Config::get('alipay.mobilesso.rsa_pri_path');
        $tokenStr = Tool_Sign::get_alipay_sign_form($data);
        $data['sign'] = Tool_Sign::get_rsa_sign($tokenStr, $rsaPath);
        $desUrl = $url .'?';
        foreach($data as $key => &$val){
            $desUrl .= $key . '=' . urlencode($val) . '&';
        }
        $desUrl .= $urlParams;
        header('Location:' . $desUrl);
        exit;
    }

    public static function cityService($data){
        $gateUrl = Comm_Config::get('alipay.cityservice.gate_url');
        $rsaPath = APPPATH . Comm_Config::get('alipay.cityservice.rsa_pri_path');
        //下面两个参数只有测试环境使用
        //$data['provider_hostname'] = Comm_Config::get('alipay.cityservice.hostname');
        //$data['sendFormat'] = 'normal';
        //end
        $tokenStr = Tool_Sign::get_alipay_sign_form($data);
        $data['sign'] = Tool_Sign::get_rsa_sign($tokenStr, $rsaPath);
        $data['sign_type'] = 'RSA';
        $request = new Comm_HttpRequest($gateUrl);
        $request->set_method('GET');
        foreach($data as $key => $val){
            $request->add_query_field($key, $val);
        }
        //echo http_build_query($data);
        $request->send();
        $res = $request->get_response_content();
        $res = json_decode($res, true);
        return $gateUrl . '?' . http_build_query($data);
        return $res;
    }
}
