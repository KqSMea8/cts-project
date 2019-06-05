<?php
/**
 * Swift 程序错误
 *
 * @package Swift
 * @subpackage exception
 * @copyright copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 */
 
class Swift_Exception_Program extends Swift_Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}
