<?php
/**
 * 抽象类，所有concrete Log_Formatter都需要扩展此类
 *
 * @package Swift
 * @subpackage log
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */

abstract class Comm_Log_Formatter {
    /**
     * @var string 日志类型，用于Comm_Log attach Log_Writer时指定的type相匹配
     */
    protected $type = "";
    
    /**
     * @var 日志内容
     */
    protected $body;
    
    /**
     * 生成一条日志
     * 
     * @var $body 日志内容
     */
    public function __construct($body) {
        $this->body = $body;
    }
    
    /**
     * 获取当前日志的类型
     */
    public function get_type() {
        return $this->type;
    }
    
    /**
     * 获取当前日志对应的syslog level
     * @see http://cn.php.net/manual/en/function.syslog.php
     */
    public function get_syslog_level() {
        return LOG_INFO;
    }
    
    /**
     * 获取当前日期和时间
     */
    public function get_date() {
        return date('ymd H:i:s');
    }
    
    /**
     * 生成日志内容
     */
    public function get_string() {
        return $this->get_type() . '-' . $this->get_date() . ' ' . $this->body;
    }
    
    /**
     * magic method
     */
    final public function __toString() {
        return $this->get_string();
    }
}