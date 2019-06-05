<?php
/**
 * 插件错误
 *
 * @package Swift
 * @subpackage swift
 * @copyright copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 */
 
class Swift_Exception_Plugin extends Swift_Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}
