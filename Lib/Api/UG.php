<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2018/9/6
 * Time: 下午4:02
 */
class Api_UG extends Api_Weibo{
    protected $connectTimeout = 3000;
    protected $timeout = 3000;

    protected $domain = 'http://api.task.weibo.com/';
    const VERSION_ID = 1;
    const KEY = 'KtDa8vK60lOJb8SD1Kd5WYj8m9MN9Maa';
    const THIRD_PARTY = 20;
    public function init() {
        if(ENVIRONMENT == 'pro'){
            $this->domain = 'http://api.task.weibo.com/';
        }else{
            $this->domain = 'http://test.api.task.weibo.com/';
        }
    }

    protected function getSign($arrPara, $key) {

        ksort($arrPara);
        reset($arrPara);
        $paraFilter = [];
        foreach ($arrPara as $k => $v) {
            if ($k == "sign" || $k == "sign_type" || $v === "" || is_null($v)) {
                continue;
            }
            $paraFilter[$k] = $arrPara[$k];
        }
        $pairs = [];
        foreach ($paraFilter as $k => $v) {
            $pairs[] = "$k=$v";
        }
        return md5(implode('&', $pairs) . $key);
    }
    function getScore($uid){
        $this->setTAuth($uid);
        $data = array(
            'source' => Model_Const::APP_SOURCE_EXCHANGE,
            'uid' => $uid,
            'version_id' => self::VERSION_ID,
        );
        $data['sign'] = $this->getSign($data, self::KEY);
        return $this->post('/score/get', $data, 'json');
    }
    function addScore($uid, $score, $orderId){
        $this->setTAuth($uid);
        $data = array(
            'source' => Model_Const::APP_SOURCE_EXCHANGE,
            'uid' => $uid,
            'version_id' => self::VERSION_ID,
            'score'=>$score,
            'thirdparty'=>self::THIRD_PARTY,
            'order_id'=>$orderId,
            //'des'=>'',
        );
        $data['sign'] = $this->getSign($data, self::KEY);
        return $this->post('/score/add', $data, 'json');
    }
    function minusScore($uid, $score, $orderId){
        $this->setTAuth($uid);
        $data = array(
            'source' => Model_Const::APP_SOURCE_EXCHANGE,
            'uid' => $uid,
            'version_id' => self::VERSION_ID,
            'score'=>$score,
            'to'=>self::THIRD_PARTY,
            'order_id'=>$orderId,
            //'des'=>'',
        );
        $data['sign'] = $this->getSign($data, self::KEY);
        return $this->post('/score/minus', $data, 'json');
    }

}