<?php

class Redis2{
    const MODE_READ = 0;
    const MODE_WRITE = 1;
    const PART_PREFIX = 'part';

    private static $_redisObj = null;
    protected $config;
    protected $mode;

    public function __construct($alias){

        $this->config = Lib_Config::get("resource.redis.$alias");
//        if (!is_array($this->config) || !isset($this->config[sprintf('%s_0', self::PART_PREFIX)]['write']) || !isset($this->config[sprintf('%s_0', self::PART_PREFIX)]['read'])) {
//            throw new Exception("Redis config Error");
//        }
        if (!is_array($this->config) || !isset($this->config['write']) || !isset($this->config['read'])) {
            throw new Exception("Redis config Error");
        }
    }
    /**
     * 获取redis
     * @param  int $mode
     * @return Redis
     * @throws Exception
     */
    protected function getRedis($part, $mode){
        if (isset(self::$_redisObj[$part][$mode])) {
            return self::$_redisObj[$part][$mode];
        }
        if ($mode == self::MODE_WRITE) {
            $server = explode(':', $this->config['write']);
        } else {
            $server = explode(':', $this->config['read']);
        }

        if (empty($server) || !is_array($server)) {
            throw new Exception("Redis Config Error");
        }
        $host = trim($server[0]);
        $port = $server[1];
        $password = $server[2];
        $redis = new Redis();
        $result = $redis->connect($host, $port);

        if ($result == false) {
            throw new Exception("Redis Connect Error");
        }

        if (!empty($password)) {
            $redis->auth($password);
        }

        self::$_redisObj[$part][$mode] = $redis;
        return self::$_redisObj[$part][$mode];
    }
    public function __call($method, $arg){
        static $readMethods = array(
            'get'       =>true,
            'hGet'      =>true,
            'zRange'    =>true,
            'lrange'    =>true,

        );
        if (!$method) {
            return false;
        }
        //echo "method={$method}";
        if(isset($readMethods[$method])){
            $mode = self::MODE_READ;
        }else{
            if(($pos = strpos($method, 'FromMaster')) > 0){//强制读主库，在方法后面加上FromMaster
                $method = substr($method, 0 , $pos);
            }
            $mode = self::MODE_WRITE;
        }
        //echo "mode={$mode}" . print_r($arg);
        
        switch (strtolower($method)) {
            case 'mget':
                return $this->mgetRewrite($arg[0]);
                break;
            
            default:
                break;
        }
        
//        $hash_val = Lib_Function::redisHash($arg[0], count($this->config));
//        $part = sprintf('%s_%d', self::PART_PREFIX, $hash_val);
        $part = 1;
        $redis = self::getRedis($part, $mode);
        if (!method_exists($redis, $method)) {
            throw new Exception("Class RedisCli not have method ($method) ");
        }

        return call_user_func_array(array($redis, $method), $arg);
    }

    /**
     * mgetRewrite 重写mget
     * @param  array  $key_list
     * @return array
     */
    private function mgetRewrite($key_list = array())
    {
        if (empty($key_list)) {
            return array();
        }

        $key_hash_map = $this->keyListHash($key_list);
        $key_val_map = array();
        foreach ($key_hash_map as $hash_val => $keys) {
//            $part = sprintf('%s_%d', self::PART_PREFIX, $hash_val);
            $part = 1;
            $redis = $this->getRedis($part, $this->mode);
            $cache_list = $redis->mget($keys);
            foreach ($keys as $index => $key) {
                $key_val_map[$key] = $cache_list[$index];
            }
        }

        $rtn = array();
        foreach ($key_list as $key) {
            $rtn[] = $key_val_map[$key];
        }

        return $rtn;
    }

    /**
     * keyListHash key hash分组
     * @param  array  $key_list
     * @return array
     */
    private function keyListHash($key_list = array())
    {
        $map = array();
        $config_count = count($this->config);
        foreach ($key_list as $key) {
            $hash_val = Lib_Function::redisHash($key, $config_count);
            $map[$hash_val][] = $key;
        }

        return $map;
    }

}
