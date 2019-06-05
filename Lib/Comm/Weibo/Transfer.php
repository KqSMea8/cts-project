<?php
/**
 * Comm_Weibo_Transfer 
 * 转账API下相关接口
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-4-29
 * @copyright copyright(2013) weibo.com all rights reserved
 * http://redmine.admin.t.sina.cn/projects/taolang-shopweibo/wiki/%E7%94%B5%E5%95%86%E9%A1%B9%E7%9B%AE
 */
class Comm_Weibo_Transfer
{
    const PROTOCOL_URL = 'http://api.sc.weibo.com/v2/transfer/income/agreement';
    const CREATE_URL = 'http://api.sc.weibo.com/v2/transfer/income/create';
    const VERIFY_URL = 'http://api.sc.weibo.com/v2/transfer/income/verify';
    const PAYER_URL = 'http://api.sc.weibo.com/v2/transfer/income/payer';
    const LIST_URL = 'http://api.sc.weibo.com/v2/transfer/income/list';
    const DETAIL_URL = 'http://api.sc.weibo.com/v2/transfer/income/detail';
    const PAY_URL = 'http://api.sc.weibo.com/v2/transfer/outcome/create';
    const NOTIFY_URL = 'http://api.sc.weibo.com/v2/transfer/outcome/notify/sync';//转账单支付结果通知(同步)
    const UPDATE_URL = 'http://api.sc.weibo.com/v2/transfer/income/update';
    const OUTCOME_URL = 'http://api.sc.weibo.com/v2/transfer/outcome/detail';
    const API_KEY = 'f4793789d78e66793ca3';
    const PAID_KEY = 'c9ca09ec04f23dab45c154053a11e185';
    const FRESH_BONUS_KEY = '2c030e8af3def8c8e947';
    const TRADE_LIST_URL = 'http://api.sc.weibo.com/v2/pay/trade/list';
    const TRADE_STATUS_URL = 'http://api.sc.weibo.com/v2/pay/trade/status';
    const TRADE_TRADE_DETAIL = 'http://api.sc.weibo.com/v2/pay/trade/tradedetail';
    const TRADE_TRANS_DETAIL = 'http://api.sc.weibo.com/v2/pay/trade/transdetail';
    const TRADE_TRANS_RECORD = 'http://api.sc.weibo.com/v2/pay/trade/record';
    const TRADE_HAS_PAID = 'http://api.sc.weibo.com/v2/pay/paieduser'; 
    const BONUS_C_CREATE = 'http://api.sc.weibo.com/v2/bonus/c/create';
    const BONUS_C_ADD =   'http://api.sc.weibo.com/v2/bonus/c/set/add';
    const BONUS_COUPON = 'http://api.c.weibo.com/interface/external/coupon/turntable';
    const BONUS_REFUND = 'http://api.sc.weibo.com/v2/pay/refund/refund';      

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
            return $request->get_response_content();
        } catch(Exception $e)
        {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    /**
     *
     * 卡券平台接口
     * @param unknown_type $url
     * @param unknown_type $platform_key
     * @param unknown_type $data
     */
    public function get_coupon_post_result($url, $platform_key, $data = array(),$needSign = true) {
        try
        {
            $request = new Comm_HttpRequest($url);
            $request->set_method('POST');
            foreach($data as $key => $val)
            {
                $request->add_post_field($key, $val);
            }
            if($needSign){
                $sign = Tool_Sign::generate_sign($data, $platform_key);
                $request->add_post_field('sign', $sign);
            }
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
     * income_agreement 
     * 是否签署转账协议
     * @param mixed $data 
     * @return void
     */
    public function income_agreement($data){
        $url = self::PROTOCOL_URL;
        $key = self::API_KEY;
        return $this->get_get_result($url, $key, $data);
    }

    /**
     * income_create 
     * 创建收款单(收款)
     * @param mixed $data 
     * @return void
     */
    public function income_create($data){
        $url = self::CREATE_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    /**
     * income_payer 
     * 获取收款单的付款列表
     * @param mixed $data 
     * @return void
     */
    public function income_payer($data){
        $url = self::PAYER_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    /**
     * income_list 
     * 获取收款单列表
     * @param mixed $data 
     * @return void
     */
    public function income_list($data){
        $url = self::LIST_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    /**
     * income_list 
     * 收款单详情
     * @param mixed $data 
     * @return void
     */
    public function income_detail($data){
        $url = self::DETAIL_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    /**
     * outcome_create 
     * 创建转账单(汇款)
     * @param mixed $data 
     * @return void
     */
    public function outcome_create($data){
        $url = self::PAY_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    /**
     * notify_sync 
     * 转账单支付结果通知
     * @param mixed $data 
     * @return void
     */
    public function notify_sync($data){
        $url = self::NOTIFY_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    //更新收款单
    public function income_update($data){
        $url = self::UPDATE_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    //实名认证
    public function income_verify($data){
        $url = self::VERIFY_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    //付款单详情
    public function outcome_detail($data){
        $url = self::OUTCOME_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    public function trade_list($data){
        $url = self::TRADE_LIST_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    public function trade_status($data){
        $url = self::TRADE_STATUS_URL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    public function trade_tradedetail($data){
        $url = self::TRADE_TRADE_DETAIL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }

    public function trade_transdetail($data){
        $url = self::TRADE_TRANS_DETAIL;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }
    public function trade_record($data){
        $url = self::TRADE_TRANS_RECORD;
        $key = self::API_KEY;
        return $this->get_post_result($url, $key, $data);
    }
    public function is_paid_user($data){
        $url = self::TRADE_HAS_PAID;
        $key = self::PAID_KEY;
        return $this->get_post_result($url, $key, $data);
    }
    public function create_bonus($data){
        $url = self::BONUS_C_CREATE;
        $key = self::FRESH_BONUS_KEY;
        return $this->get_post_result($url, $key, $data);
    }
    public function add_bonus($data){
        $url = self::BONUS_C_ADD;
        $key = self::FRESH_BONUS_KEY;
        return $this->get_post_result($url, $key, $data);
    }
    public function importcoupon($data,$key){
        $url = self::BONUS_COUPON;
        //$key = self::FRESH_BONUS_KEY;
        $sign = hash_hmac('sha1', $data['merchantUid']. $data['ts'], $key);
        $data['s'] = $sign;
       return $this->get_coupon_post_result($url, $key, $data, false); 
    } 
    public function bonus_refund($data){
        $url = self::BONUS_REFUND;
        $key = self::FRESH_BONUS_KEY;
       return $this->get_post_result($url, $key, $data); 
    }  
}
