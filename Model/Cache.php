<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2018/9/4
 * Time: 下午2:56
 */
class Model_Cache{
    protected static $_redisObj = null;


    /**
     * @param $alias
     * @return Redis
     * @throws Exception
     */
    protected static function getRedis(){
        if(is_null(self::$_redisObj)){
            self::$_redisObj = new Redis2(Model_Const::REDIS_ALIAS_EXCHANGE);
        }
        return self::$_redisObj;
    }

    protected static function getUserScoreKey($uid){
        return sprintf(Lib_Config::get('DataKey.user_score'), $uid);
    }
    public static function setUserScore($uid, $score){
        return self::getRedis()->set(self::getUserScoreKey($uid), $score, Model_Const::TTL_ONE_MINUTE);
    }
    public static function getUserScore($uid){
        return self::getRedis()->get(self::getUserScoreKey($uid));
    }

    protected static function getUserJfKey($uid){
        return sprintf(Lib_Config::get('DataKey.user_jf'), $uid);
    }
    public static function setUserJf($uid, $score){
        return self::getRedis()->set(self::getUserJfKey($uid), $score, Model_Const::TTL_ONE_MINUTE);
    }
    public static function getUserJf($uid){
        return self::getRedis()->get(self::getUserJfKey($uid));
    }

    protected static function getAppTokenKey($appKey){
        return sprintf(Lib_Config::get('DataKey.app_token'), $appKey);
    }
    public static function getAppToken($appKey){
        try {
            $key = self::getAppTokenKey($appKey);
            $cachedData = self::getRedis()->get($key);
            if($cachedData){
                $ret = json_decode($cachedData, true);
                if(isset($ret['expired_time']) && $ret['expired_time'] >= time()){
                    return $ret;
                }
            }
            $token = Data_Etc::getAppToken($appKey);
            if(empty($token)){
                return false;
            }
            self::getRedis()->set($key, $token, Model_Const::TTL_ONE_HOUR);
            $ret = json_decode($token, true);
            return $ret;

        } catch (Exception $e) {
            Lib_Log::fatal($e->getMessage());
            return false;
        }
    }
    public static function getJsVersionKey($type){
        return sprintf(Lib_Config::get('DataKey.js_version'), $type);
    }

    public static function setJsVersion($type, $version){
        return self::getRedis()->set(self::getJsVersionKey($type), $version);
    }
    public static function getJsVersion($type){
        return self::getRedis()->get(self::getJsVersionKey($type));
    }
    public static function delJsVersion($type){
        return self::getRedis()->del(self::getJsVersionKey($type));
    }

    public static function getTodayCountKey($uid)
    {
        return sprintf(Lib_Config::get('DataKey.today_count'), $uid, date('Ymd'));
    }

    public static function getTodayCount($uid)
    {
        return self::getRedis()->get(self::getTodayCountKey($uid));
    }

    public static function setTodayCount($uid, $count)
    {
        return self::getRedis()->set(self::getTodayCountKey($uid), $count, Model_Const::TTL_ONE_DAY);
    }

    public static function incrTodayCount($uid, $count)
    {
        $key = self::getTodayCountKey($uid);

        $count = self::getRedis()->incrby($key, $count);
        self::getRedis()->expire($key, Model_Const::TTL_ONE_DAY);

        return $count;
    }

    public static function decrTodayCount($uid, $count)
    {
        $key = self::getTodayCountKey($uid);

        $count = self::getRedis()->decrby($key, $count);
        self::getRedis()->expire($key, Model_Const::TTL_ONE_DAY);

        return $count;
    }

    public static function getTodayCashPoolingKey()
    {
        return sprintf(Lib_Config::get('DataKey.today_cash_pooling'), date('Ymd'));
    }

    public static function getTodayCashPoolingCount()
    {
        return self::getRedis()->get(self::getTodayCashPoolingKey());
    }

    public static function setTodayCashPoolingCount($count)
    {

        return self::getRedis()->set(self::getTodayCashPoolingKey(), $count, Model_Const::TTL_ONE_DAY);
    }

    public static function incrTodayCashPoolingCount($count)
    {
        $key = self::getTodayCashPoolingKey();

        $pcount = self::getRedis()->incrby($key, $count);
        self::getRedis()->expire($key, Model_Const::TTL_ONE_DAY);

        return $pcount;
    }

    public static function decrTodayCashPoolingCount($count)
    {
        $key = self::getTodayCashPoolingKey();

        $pcount = self::getRedis()->decrby($key, $count);
        self::getRedis()->expire($key, Model_Const::TTL_ONE_DAY);

        return $pcount;
    }
    protected static function getExchangeMsgKey() {
        return Lib_Config::get('DataKey.push_msg_list');
    }
    public static function pushExchangeMsg($msg)
    {
        $key = self::getExchangeMsgKey();
        return self::getRedis()->lPush($key, $msg);
    }
    public static function popExchangeMsg(){
        $key = self::getExchangeMsgKey();
        return self::getRedis()->rPop($key);
    }
    protected static function getUserMsgKey() {
        return sprintf(Lib_Config::get('DataKey.user_msg_list'), date('Ymd'));
    }
    public static function pushUserMsg($msg)
    {
        $key = self::getUserMsgKey();
        return self::getRedis()->rPush($key, $msg);
    }
    public static function getUserMsg($start, $len=20){
        $end = $start + $len - 1;
        $key = self::getUserMsgKey();
        return self::getRedis()->lRange($key, $start, $end);
    }
    protected static function getWeiboInfoKey($uid){
        return sprintf(Lib_Config::get('DataKey.user_info_by_uid'), $uid);
    }
    public static function getWeiboInfo($uid){
        $key = self::getWeiboInfoKey($uid);
        return self::getRedis()->get($key);
    }
    public static function setWeiboInfo($uid, $info){
        $key = self::getWeiboInfoKey($uid);
        return self::getRedis()->set($key, $info, Model_Const::TTL_ONE_HOUR);
    }

    protected static function getRecommendKey($prefix)
    {
        return sprintf(Lib_Config::get('DataKey.recommend_list'), $prefix);
    }

    public static function setRecommend($prefix, $data)
    {
        $key = static::getRecommendKey($prefix);

        static::getRedis()->set($key, json_encode($data));
        self::getRedis()->expire($key, Model_Const::TTL_ONE_MINUTE);
        return true;
    }

    public static function getRecommend($prefix)
    {
        $key = static::getRecommendKey($prefix);

        $result =  static::getRedis()->get($key);

        return json_decode($result, true);
    }
}