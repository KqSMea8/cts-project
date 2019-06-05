<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2018/12/19
 * Time: 10:31 AM
 */
class Model_JF{

    const MID_DH = '6898571469';//兑换MID
    const MID_DXJ = '6898573447';//兑现金MID
    const MID_DEV = '3292350247';//开发环境
    const BONUS_KEY = '322e5de425shhgz2ows0j6';//发红包key
    const BONUS_TPL_ID = '5000000033368';//发红包魔板

    const TYPE_CONSUME_DX = 10;
    const TYPE_CONSUME_DXJ = 11;
    protected static function buildOid($uid, $orderId, $type){
        return $orderId . '_' . $type . '_' . ($uid%128);
    }
    public static function getScoreDx($uid, $fromCache = true){
        if(empty($uid)){
            return 0;
        }
        if($fromCache) {
            $score = Model_Cache::getUserJf($uid);
            if (!empty($score)) {
                return $score;
            }
        }
        $api = new Api_JF();
        $result = $api->getUserScore(self::MID_DH, $uid);
        //Lib_Log::info("Model_JF::getScore|{$uid}|" . json_encode($result));
        if($result['code'] == '100000'){
            Model_Cache::setUserJf($uid, $result['data']['data']['legal']);
            return $result['data']['jf'];
        }
        Lib_Log::warning("Model_JF::getScore error|{$uid}|" . json_encode($result));
        return false;
    }
    public static function consumeDx($uid, $orderId, $score, $subject){
        $api = new Api_JF();
        $orderId = self::buildOid($uid, $orderId, self::TYPE_CONSUME_DX);
        $result = $api->consume(self::MID_DH, $uid, $orderId, $score, $subject);
        if($result['code'] == '100000'){
            return $result['data']['consume_id'];
        }
        Lib_Log::warning("Model_JF::consume error|{$uid}, {$orderId}|" . json_encode($result));
        return false;
    }
    public static function consumeQueryDx($uid, $orderId){
        $api = new Api_JF();
        $orderId = self::buildOid($uid, $orderId, self::TYPE_CONSUME_DX);
        return $api->consumeQuery(self::MID_DH, $orderId);
    }

    public static function getScoreDxj($uid, $fromCache = true){
        if(empty($uid)){
            return 0;
        }
        if($fromCache) {
            $score = Model_Cache::getUserJf($uid);
            if (!empty($score)) {
                return $score;
            }
        }
        $api = new Api_JF();
        $result = $api->getUserScore(self::MID_DXJ, $uid);
        if($result['code'] == '100000'){
            Model_Cache::setUserJf($uid, $result['data']['data']['legal']);
            return $result['data']['jf'];
        }
        Lib_Log::warning("Model_JF::getScore error|{$uid}|" . json_encode($result));
        return false;
    }
    public static function consumeDxj($uid, $orderId, $score, $subject){
        $api = new Api_JF();
        $orderId = self::buildOid($uid, $orderId, self::TYPE_CONSUME_DXJ);
        $result = $api->consume(self::MID_DXJ, $uid, $orderId, $score, $subject);
        if($result['code'] == '100000'){
            return $result['data']['consume_id'];
        }
        Lib_Log::warning("Model_JF::consume error|{$uid}, {$orderId}|" . json_encode($result));
        return false;
    }
    public static function consumeQueryDxj($uid, $orderId){
        $api = new Api_JF();
        $orderId = self::buildOid($uid, $orderId, self::TYPE_CONSUME_DXJ);
        return $api->consumeQuery(self::MID_DXJ, $orderId);
    }
    public static function verifyApplyDxj($uid, $orderId, $score){
        $api = new Api_JF();
        $orderId = self::buildOid($uid, $orderId, self::TYPE_CONSUME_DXJ);
        return $api->verifyApply(self::MID_DXJ, $orderId, $score, '积分兑红包核销');
    }
}