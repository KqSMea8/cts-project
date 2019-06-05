<?php
/**
 * 日志接口
 */

/**
 * Interface Sso_Sdk_Tools_Logger_ILogger
 */
interface Sso_Sdk_Tools_Logger_ILogger {
	/**
	 * 记录日志
	 * @param $level int
	 * @param $type string
	 * @param $msg string
	 * @return mixed
	 */
	public function log($level, $type, $msg);
}