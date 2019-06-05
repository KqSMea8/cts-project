<?php
/**
 * File log writer. 一天一文件
 *
 * @package Swift
 * @subpackage log
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */

class Comm_Log_Writer_File extends Comm_Log_Writer {
    
    /**
     * @var string 日志存放目录
     */
    private $directory;
    
    /**
     * 生成实例，并检查指定的路径是否可用
     *
     * @param   string log directory
     * @return  void
     */
    public function __construct($directory) {
        if (!is_dir($directory) or !is_writable($directory)) {
            throw new Comm_Exception_Program('log directory not available');
        }
        
        $this->directory = realpath($directory);
    }
    
    /**
     * 将日志写入文件尾
     *
     * @param array 多个Log_Formatter实例
     * @return  void
     */
    public function write(array $formatters) {
        if (empty($formatters)) {
            return;
        }
        
        $filename = $this->get_filename();
        
        if (!file_exists($filename)) {
            file_put_contents($filename, 'BEGIN');
            chmod($filename, 0666);
        }
        
        foreach ($formatters as $formatter) {
            file_put_contents($filename, PHP_EOL . $formatter, FILE_APPEND);
        }
    }
    
    /**
     * 生成今天的日志文件名
     */
    public function get_filename(){
        return $this->directory . DIRECTORY_SEPARATOR . date('ymd') . '.log';
    }
}
