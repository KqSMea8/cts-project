<?php
/**
 * 发号器
 */
class Lib_Uuid {

    const UUID_TIME_BASE         = 515483463;         //计算UUID的时间基准

    const B_TYPE_CHARGE          = 'CHARGE';           //充值
    const B_TYPE_ORDER           = 'ORDER';             //订单
    const B_TYPE_USER            = 'USER';              //用户
    const B_TYPE_PRODUCT_ORDER   = 'PRODUCT_ORDER';     //奖品订单
    const B_TYPE_MARKET_ORDER    = 'MARKETORDER';       //开心超市订单
    const B_TYPE_DICE_ORDER    = 'DICEORDER';       //骰子订单
    const B_TYPE_DIAL_ORDER    = 'DIALORDER';       //开心转盘订单
    const B_TYPE_BATTLE_ORDER    = 'BATTLEORDER';       //开心斗法
    const B_TYPE_BATTLE_ROUND    = 'BATTLEROUND';       //开心斗法
    const B_TYPE_BATTLE_ROOM    = 'BATTLEROOM';       //开心斗法
    const B_TYPE_FISH_ORDER    = 'FISHORDER';       //开心钓鱼订单

    public static $type_array = array(
        self::B_TYPE_CHARGE          => 50,
        self::B_TYPE_ORDER           => 51,
        self::B_TYPE_USER            => 52,
        self::B_TYPE_PRODUCT_ORDER   => 53,
        self::B_TYPE_MARKET_ORDER    => 54,
        self::B_TYPE_DICE_ORDER    => 56,
        self::B_TYPE_DIAL_ORDER    => 55,
        self::B_TYPE_BATTLE_ROOM    => 57,
        self::B_TYPE_BATTLE_ROUND    => 58,
        self::B_TYPE_BATTLE_ORDER    => 59,
        self::B_TYPE_FISH_ORDER    => 60,
    );


    /*
     * 获取UUID
     * 注：先通过Redis缓存生成UUID,如果生成失败，再通过平台API取得UUID
    */
    public static function gen_uuid($type) {

        if(!array_key_exists($type, self::$type_array)){
            throw new Exception("发号器发号失败：非法的业务类型！");
        }
        $prefix = self::$type_array[$type];

        $from_mc_start_time = microtime(true);
        $uuid = self::gen_uuid_from_mc();
        if ($uuid) {
           // Tool_Log::info("GENERATE UUID FROM MC SUCCEEDED. uuid={$uuid} type={$type} time= ".((microtime(true) - $from_mc_start_time) * 1000)." ms");
        }

        if (!empty($uuid)) {
            return $prefix.$uuid;
        }else{
            throw new Exception("发号器发号失败：系统异常");
        }

    }


    /*
     * 通过缓存(Redis)生成UUID
     * 注：UUID共占用52Bit, 30Bit存储秒级时间戳(可以支持到2020年)，4Bit存储版本号(Reids机器编号)，18Bit序列号
     * Author: jiuyi
     */
    public static function gen_uuid_from_mc(){
        try {
            $uuid_module  = 'uuid';           //uuid redis模块
            $expire_time  = 30;               //key过期时间 暂定30s
            $cur_time = time();
            $cur_second = date('s', $cur_time);
            $key = 'uuid_'.$cur_second;

            //如果redis 0.5秒内没有返回,返回false
            //$redis = Data_Spliter::hash_redis($uuid_module, $cur_second, true, 0.5);
            $redis = new Lib_Redis('jingcai', Lib_Redis::MODE_WRITE);
            if (empty($redis)) {
                //Tool_Log::warning("GENERATE UUID FROM MC CONNECT REDIS FAILED");
                return false;
            }

            if ($redis->exists($key)) {
                $uuid_increment = $redis->incr($key);
            } else {
                //第一次设置序列号时，需要设置key的过期时间
                $uuid_increment = $redis->incr($key);
                $redis->expire($key, $expire_time);
            }

            if ($uuid_increment) {
                $uuid_increment = 0x3FFFF & $uuid_increment;   //18Bit序列号
                $uuid_version = 0xF & 1;               //4Bit版本号    注：0xC已被平台占用
                //if ($uuid_version == 0xC) {
                    //Tool_Log::warning("GENERATE UUID FROM MC FAILED.  VERSION NUMBER CONFLICTED WITH PLATFORM");
                //    return false;
                //}
                $time = $cur_time - self::UUID_TIME_BASE;         //10Bit 处理后的时间戳
                $uuid = ( ($time << 22) | ($uuid_version << 18) | $uuid_increment);
                return $uuid;
            } else {
                return false;
            }

        } catch (Exception $e) {
            //Tool_Log::fatal("GENERATE UUID FROM MC EXCEPTION.  msg=".$e->getMessage());
         
            return false;
        }
    }

   
    /**
     * 根据uuid解析出时间戳
     * @param $uuid
     */
    public static function parse_timestamp($uuid){
        if(strlen($uuid) != 18){
            if(strpos($uuid,'-')){         //时间格式: Y-m-d
                return strtotime($uuid);
            }else{                         //timestamp
                return $uuid;
            }
        }else{                             //标准18位uuid
            $uuid = substr($uuid,2);
            return ($uuid >> 22) + self::UUID_TIME_BASE;
        }
    }

    public static function analysis_uuid ( $uuid ) {
        if ( 18 != strlen( $uuid ) ) {
            return array();
        }
        //uuid = $type (2位) + UUID共占用52Bit, 30Bit存储秒级时间戳(可以支持到2020年)，4Bit存储版本号(Reids机器编号)，18Bit序列号
        else {
            $type = substr( $uuid, 0, 2 );
            $uuid = substr( $uuid, 2 );
            $timestamp = self::UUID_TIME_BASE + ( $uuid >> 22 );
            $uuid_version = $uuid >> 18 & 0xF;
            $uuid_increment = $uuid & 0x3FFFF;
            $ret = array(
                'type' => $type,
                'timestamp' => $timestamp,
                'time' => date("Y-m-d H:i:s", $timestamp),
                'uuid_version' => $uuid_version,
                'uuid_increment' => $uuid_increment
            );
            return $ret;
        }
    }

}
