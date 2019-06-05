<?php
/**
 * Swift 断言错误
 *
 * @package Swift
 * @subpackage exception
 * @copyright copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 */

class Comm_Assert_Exception extends Comm_Exception_Program {
	public function __construct($message) {
		parent::__construct ( $message );
	}
}