<?php
/**
 * Created by JetBrains PhpStorm.
 * User: shiliang5
 * Date: 15-1-8
 * Time: 下午6:35
 * 【用微博支付赢天天免单】相关抽奖接口、中奖信息查询接口等
 */
class Comm_Weibo_Payfree {
    const URL = '';
    const DRAW_URL = 'http://api.sc.weibo.com/v2/order/reward/reward';
    const LOTTERY_INFO_URL = 'http://api.sc.weibo.com/v2/order/reward/list';
    const PAY_FREED_URL = 'http://api.sc.weibo.com/v2/order/reward/payfree';
    const TRADE_LIST_URL = 'http://api.sc.weibo.com/v2/order/reward/tradelist';
//    const DRAW_URL = 'http://xiangjiuyi.com/v2/order/reward/reward';
//    const LOTTERY_INFO_URL = 'http://xiangjiuyi.com/v2/order/reward/list';
//    const PAY_FREED_URL = 'http://xiangjiuyi.com/v2/order/reward/payfree';
//    const TRADE_LIST_URL = 'http://xiangjiuyi.com/v2/order/reward/tradelist';
    const API_KEY = 'fac837511ae2417cf319';

    public function get_post_result($url, $platform_key, $data = array()) {
        try
        {
            $request = new Comm_HttpRequest($url);
            $request->set_method('POST');
            foreach($data as $key => $val) {
                $request->add_post_field($key, $val);
            }

            $sign = Tool_Sign::generate_sign($data, $platform_key);
            $request->add_post_field('sign', $sign);
            $res = $request->send();
            return $request->get_response_content();
        } catch(Exception $e) {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    public function get_get_result($url, $platform_key, $data = array()){
        try{
            $request = new Comm_HttpRequest($url);
            $request->set_method('GET');
            foreach($data as $key => $val) {
                $request->add_query_field($key, $val);
            }

            $sign = Tool_Sign::generate_sign($data, $platform_key);
            $request->add_query_field('sign', $sign);
            $res = $request->send();
            return $request->get_response_content();
        } catch(Exception $e) {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    /**
     * @param $data
     * @return array
     * 查询已抽中免单的用户相关信息，由于展示“看看谁中奖了”
     */
    public function get_pay_freed_uid ($data) {
        $url = self::PAY_FREED_URL;
        $key = self::API_KEY;
        $ret = $this->get_post_result($url, $key, $data);
        $ret = json_decode($ret, true);
    /*    $payfreed = array (
            'code' => '100000',
            'data' => array(
                0 => array(
                    'uid' => '3292349193',
                    'create_time' => '2015-01-08',
                    'value' => '1009.12',
                ),
                1 => array(
                    'uid' => '1111681197',
                    'create_time' => '2015-01-08',
                    'value' => '1009.12',
                ),
                2 => array(
                    'uid' => '2002106280',
                    'create_time' => '2015-01-08',
                    'value' => '1009.12',
                ),
                3 => array(
                    'uid' => '1211410124',
                    'create_time' => '2015-01-08',
                    'value' => '1009.12',
                ),
                4 => array(
                    'uid' => '1211410124',
                    'create_time' => '2015-01-08',
                    'value' => '1009.12',
                ),
            ),
        );*/
        return $ret;
    }

    /**
     * @param $data
     * @return array
     * 获取当前用户的当前抽奖接口
     */
    public function get_lottery_info ($data) {
        $url = self::DRAW_URL;
        $key = self::API_KEY;
        $ret = $this->get_post_result($url, $key, $data);
        $ret = json_decode($ret, true);
       /* $ret = array(
            'code' => '100000',
            'data' => array(
                'status' => '2',
                'value' => '11',
            ),

        );*/
        return $ret;
    }

    /**
     * @param $data
     * @return array
     * 获取当前用户已抽奖的相关信息
     */
    public function get_lottery_by_uid ($data) {
        $url = self::LOTTERY_INFO_URL;
        $key = self::API_KEY;
        $ret = $this->get_post_result($url, $key, $data);
        $ret = json_decode($ret, true);
        /*$freed_orders = array(
            'code' => '100000',
            'data' => array(
                'time' => '1',
                'list' => array(
                    '0' => array(
                        'oid' => '3020401111883',
                        'status' => '1',
                    ),
                ),

            ),
        );*/
        return $ret;
    }

    /**
     * @param $data
     * @return mixed
     * 获取用户活动期间所有的支付成功的支付单数据
     *
     */
    public function get_trade_list ($data) {
        $url = self::TRADE_LIST_URL;
        $key = self::API_KEY;
        $ret = $this->get_post_result($url, $key, $data);
        $ret = json_decode($ret, true);
        return $ret;
    }

}