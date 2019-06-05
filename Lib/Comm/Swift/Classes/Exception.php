<?php
/**
 * Swift 错误处理类
 *
 * @package    Swift
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Swift_Exception extends Exception {
    protected $type_code = '100';
    
    public function __construct($message) {
        parent::__construct(strval($message), intval($this->debug_code()));
    }
    
    public function debug_code() {
        // 将错误类型、文件做素材进行摘要算法，生成8位唯一码用于排错
        // xxxxyyyy
        // TODO 文件号可通过脚本自动生成对照表
        return $this->type_code.$this->get_file_code();
    }
    
    public function get_file_code(){
        // $this->file;
        return '1000';
    }
    
    /**
     * 输出exception信息
     *
     * @return  string
     */
    public function __toString() {
        return Swift_Core::exception_text($this);
    }
}
