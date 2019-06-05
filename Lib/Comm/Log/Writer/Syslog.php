<?php
/**
 * Syslog log writer
 *
 * @package Swift
 * @subpackage log
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */

class Comm_Log_Writer_Syslog extends Comm_Log_Writer {
    /**
     * @see http://cn.php.net/manual/en/function.openlog.php
     */
    private $ident;
    
    /**
     * @var 用于syslogd指定写入设施，与日志存储方式有关，具体可查看/etc/syslog.conf配置
     */
    private $facility;
	
    private $option;
    
    private $flag;
    
    /**
     * 创建syslog实例，默认facility为LOG_USER
     *
     * @param string ident
     * @param int facility of syslogd
     * @return void
     */
    public function __construct($ident = 'php', $option = LOG_CONS, $facility = LOG_USER) {
        $this->ident = $ident;
        $this->option = $option;
        $this->facility = $facility;
    }
    
    /**
     * 将日志写入syslog，syslog_level由$formatter决定
     * @see http://cn.php.net/manual/en/function.syslog.php
     *
     * @param   array   messages
     * @return  void
     */
    public function write(array $formatters) {
        if ($this->flag !== true) {
            openlog($this->ident, $this->option, $this->facility);
            $this->flag = true;
        }
        
        foreach ($formatters as $formatter) {
            syslog($formatter->get_syslog_level(), (string)$formatter);
        }
    }

}