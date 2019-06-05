<?php
/**
 * 功能描述
 * @author: zhongfeng <zhongfeng@staff.weibo.com>
 * @date: 2017/3/13
 */
class JsVersion{

    public static function getVersion(){
        $type = 14;
        $ret = Model_Cache::getJsVersion($type);
        if(!empty($ret)){
            return $ret;
        }
        $version = self::randStr();
        Model_Cache::setJsVersion($type, $version);
        return $version;
    }
    protected static function randStr($len = 16) {

        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

        mt_srand((double)microtime() * 1000000 * getmypid());
        $randCode = '';
        while (strlen($randCode) < $len) {
            $randCode .= substr($chars, (mt_rand() % strlen($chars)), 1);
        }
        return $randCode;
    }
}