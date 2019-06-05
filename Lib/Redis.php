<?php

class Lib_Redis extends Redis {

    const MODE_READ = 0 ;
    const MODE_WRITE = 1 ;

    const CONNECT_TIMEOUT = 3;

    protected $redis;

    public function __construct($alias, $mode = self::MODE_WRITE) {
        if($mode = $mode = self::MODE_WRITE) {
            $config = Lib_Config::get("resource.redis.$alias.write");
        } else {
            $config = Lib_Config::get("resource.redis.$alias.read");
        }

        $server = explode( ':', $config );
        if( empty($server) || !is_array($server) ) {
            throw new Exception("Redis Config Error:{$alias}--{$config}");
        }
        $host = trim($server[0]);
        $port = $server[1];

        parent::__construct();

        $redisObj = new Redis();
        $result = $this->connect($host, $port, self::CONNECT_TIMEOUT);
        if(!empty($server[2])){
            $this->auth($server[2]);
        }
        if ($result == false)
        {
            throw new Exception("Connect Redis Error:{$alias}--{$config}");
        }

        $redisObj->connect($host, $port, self::CONNECT_TIMEOUT);
        if(!empty($server[2])){
            $redisObj->auth($server[2]);
        }
        $this->redis = $redisObj;

        return ;
    }


    /**
     * 读队列
     * @param  string $key
     * @param  int $btime 阻塞时间
     * @return bool|string
     */
    public function read_queue($key, $btime = 0)
    {
        $ret = false;
        try {
            if ($this->redis) {
                if ($btime > 0) {
                    $ret = $this->redis->brPop($key, $btime);
                }else {
                    $ret = $this->redis->rPop($key);
                }
            }
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
        }
        return $ret;
    }

    /**
     * 写队列
     * @param $key
     * @param $val
     * @return bool|int
     */
    public function write_queue($key, $val)
    {
        $ret = false;
        try {
            if ($this->redis) {
                $ret = $this->redis->lPush($key, $val);
            }
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
        }
        return $ret;
    }


    /**
     * 清空队列
     * @param $key
     * @return bool
     */
    public function clean_queue($key)
    {
        $ret = false;
        try {
            if ($this->redis) {
                $ret = $this->redis->lTrim($key, 0, 1);
                $ret = $this->redis->lTrim($key, 2, 3);
            }
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
        }
        return $ret;
    }


    /**
     * 获取队列剩余记录数
     * @param $key
     * @return int
     */
    public function get_len($key) {
        $ret = 0;
        try {
            if ($this->redis) {
                $ret = $this->redis->lLen($key);
            }
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
        }
        return $ret;
    }

    /**
     * 获取队列中的全部数据
     * @param type $key
     * @return array
     */
    public function get_values($key){
        $ret = 0;
        try {
            if ($this->redis) {
                $ret = $this->redis->lrange($key, 0, -1);
            }
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
        }

        return $ret;
    }

    /**
     * 删除指定的键
     * @param type $key
     * @return type
     */
    public function rmKey($key){
        $ret = 0;
        try {
            if ($this->redis) {
                $ret = $this->redis->delete($key);
            }
        } catch (Exception $e) {
            Lib_Log::warning($e->getMessage());
        }

        return $ret;
    }
}
