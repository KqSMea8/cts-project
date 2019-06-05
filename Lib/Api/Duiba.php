<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2019/2/18
 * Time: 11:43 AM
 */
class Api_Duiba extends Api_Abstract {

    protected $connectTimeout = 3000;
    protected $timeout = 3000;

    protected $domain = 'https://activity.m.duiba.com.cn';
    const APP_KEY = '3CSEoZoq4cordiHd262hdJvBL9p5';
    const APP_SECRET = '3fGQ2bShWzw77d3vCdMHqZRtoDiH';

    const OUT_ORDER_EXCHANGE = "duiba_exchange";

    protected function getSign($params){

        $params['appSecret'] = self::APP_SECRET;
        ksort($params);
        reset($params);
        $str = '';
        foreach ($params as $val){
            $str .= $val;

        }
        return md5($str);
    }
    public function queryItem($count,$period = 0){
        $params = array(
            'appKey'    =>self::APP_KEY,
            'timestamp' =>intval(microtime(true) * 1000),
            'count'     =>$count,
        );
        if($period != 0) {
            $params['period'] = $period;
        }
        $params['sign'] = $this->getSign($params);
        return $this->get('/queryForFrontItem/query', $params, self::RETURN_TYPE_JSON);
    }

    public function checkRecord($params) {
        if($params['appKey'] != self::APP_KEY) {
            return 'appkey 错误';
        }

        $query = array(
            'appKey' => $params['appKey'],
            'uid' => $params['uid'],
            'recordId' => $params['recordId'],
            'title' => $params['title'],
            'logoUrl' => $params['logoUrl'],
            'recordDetailUrl' => $params['recordDetailUrl'],
            'credits' => $params['credits'],
            'timestamp' => $params['timestamp'],
        );

        $sign = $this->getSign($query);
        if($sign != $params['sign']) {
            return 'sign 错误';
        }

        return 'ok';

    }

    /*
    *  生成自动登录地址
    *  通过此方法生成的地址，可以让用户免登录，进入积分兑换商城
    */
    public function getAutoLogin($uid = 0,$redirect = "") {
        $url = $this->domain."/autoLogin/autologin?";
        if(empty($uid)) {
            $score = 0 ;
        }else {
            //获取用户当前积分
            $score = Model_JF::getScoreDx($uid);
        }
        $params = array(
            'appKey'    =>self::APP_KEY,
            'uid' => $uid,
            'timestamp' =>intval(microtime(true) * 1000),
            'credits'     =>$score,
        );
        if(!empty($redirect)) {
            $params['redirect'] = $redirect;
        }
        $params['sign'] = $this->getSign($params);
        //$this->setDebug(true);
        $url = self::AssembleUrl($url,$params);
        return $url;
    }


    /*
    *构建参数请求的URL
    */
    public static function AssembleUrl($url, $array)
    {
        unset($array['appSecret']);
        foreach ($array as $key=>$value) {
            $url=$url.$key."=".urlencode($value)."&";
        }
        return $url;
    }
}