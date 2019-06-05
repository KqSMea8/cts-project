<?php
/**
 * Swift 404错误
 *
 * @package Swift
 * @subpackage exception
 * @copyright copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 */
 
class Swift_Exception_404 extends Swift_Exception {
    public function __construct($uri) {
        parent::__construct("page /".$uri." not found");
    }
}
