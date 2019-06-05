<?php
/**
 * 抽象类，所有concrete Log_Writer都需要扩展此类
 *
 * @package Swift
 * @subpackage log
 * @copyright copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 */

abstract class Comm_Log_Writer {
    
    /**
     * 写日志
     *
     * @param array Log_Formatter实例数组
     * @return void
     */
    abstract public function write(array $logs);
    
    /**
     * 为对象生成唯一码
     *
     * @return string
     */
    final public function __toString() {
        return spl_object_hash($this);
    }

}
