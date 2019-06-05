<?php
/**
 * @copyright   Weibo.com 2013
 */
class Comm_Timecuter
{
    /**
     * 用于存在时间数据：开始时间和结束时间
     * 
     * @var array {
     *      start : 开始时间
     *      end : 结束时间
     *      }
     */
    protected static $data = array ();
    /**
     * 执行的时间，us。
     * 
     * @var array {
     *      $name => $intTimeUs
     *      }
     */
    protected static $executionTime = array ();
    protected static $timeS = null;
    protected static $timeG = null;
    
    public static function globalBegin()
    {
        self::$timeG = gettimeofday ();
    }
    
    public static function getTimeElapse()
    {
        if (empty ( self::$timeG ))
        {
            self::$timeG = gettimeofday ();
            return 0;
        }
        $now = gettimeofday ();
        return self::getUtime ( self::$timeG, $now );
    }
    
    /**
     * 计时开始。
     * 
     * @param string $name            
     */
    public static function start($name)
    {
        self::$data [$name] ['end'] = self::$data [$name] ['start'] = gettimeofday ();
    }
    
    /**
     * 计时结束
     * 
     * @param string $name            
     */
    public static function end($name)
    {
        self::$data [$name] ['end'] = gettimeofday ();
    }
    
    /**
     * 计算执行时间，如果没有指定$name，则返回所有的执行时间数据。
     * 
     * @param string $name            
     */
    public static function calculate($name = '')
    {
        if (! empty ( $name ))
        {
            return self::_calculateOne ( $name );
        }
        else
        {
            return self::_calculateAll ();
        }
    }
    
    public static function getTimes()
    {
        return self::$executionTime;
    }
    
    public static function getNowTime()
    {
        if (is_null ( self::$timeS ))
        {
            self::$timeS = time ();
        }
        return self::$timeS;
    }
    
    /**
     * 计算时间
     * 
     * @param unknown_type $start            
     * @param unknown_type $end            
     */
    
    public static function getUtime($start, $end)
    {
        return ($end ['sec'] - $start ['sec']) * 1000 * 1000 + ($end ['usec'] - $start ['usec']);
    }
    
    public static function formatUs($time)
    {
        if ($time < 1000)
        {
            $str = $time . 'us';
        }
        elseif ($time >= 1000 && $time <= 1000000)
        {
            $str = intval ( $time / 1000 ) . 'ms';
        }
        else
        {
            $s = intval ( $time / 1000000 );
            $m = intval ( ($time - $s * 1000000) / 1000 );
            $str = $s . 's' . $m . 'ms';
        }
        return $str;
    }
    
    public static function toString($chr1 = ':', $chr2 = '|', $limit = 0)
    {
        $ret = self::calculate ();
        if (empty ( $ret ))
            return '';
        $result = '';
        foreach ( $ret as $name => $time )
        {
            if ($time < $limit)
            {
                continue;
            }
            if ($time < 1000)
            {
                $str = $time . 'us';
            }
            else
            {
                $str = intval ( $time / 1000 ) . 'ms';
            }
            $result .= sprintf ( '%s%s%s%s', $name, $chr1, $str, $chr2 );
        }
        $result = rtrim ( $result, $chr2 );
        return $result;
    }
    
    protected static function _calculateAll()
    {
        if (empty ( self::$data ))
            return array ();
        foreach ( self::$data as $name => $times )
        {
            if (! isset ( self::$executionTime [$name] ))
            {
                self::$executionTime [$name] = self::_getUtime ( $times ['start'], $times ['end'] );
            }
        }
        return self::$executionTime;
    }
    
    protected static function _calculateOne($name)
    {
        if (isset ( self::$executionTime [$name] ))
        {
            return self::$executionTime [$name];
        }
        if (isset ( self::$data [$name] ))
        {
            self::$executionTime [$name] = self::_getUtime ( self::$data [$name] ['start'], self::$data [$name] ['end'] );
            return self::$executionTime [$name];
        }
        return 0;
    }
    
    protected static function _getUtime($time1, $time2)
    {
        return ($time2 ['sec'] - $time1 ['sec']) * 1000 * 1000 + ($time2 ['usec'] - $time1 ['usec']);
    }
}