<?php
/**
 * 记录日志的文件实现，基本用于问题调试
 */

class Sso_Sdk_Tools_Logger_File implements Sso_Sdk_Tools_Logger_ILogger {

	private $_file;

	public function __construct($file) {
		$this->_file = $file;
		if (file_exists($file) && !is_writable($file)) {
			throw new Exception($file ." can not writable");
		}
		$dir = dirname($file);
		if (!file_exists($dir)) {
			if (!mkdir($dir, 0666, true)) {
				throw new Exception("mkdir $dir fail");
			}
		}
		if (is_dir($file)) {
			throw new Exception("$file should be a file ,but a dir now");
		}
	}
	/**
	 * 记录日志
	 * @param $level int
	 * @param $type string
	 * @param $msg string
	 * @throws Exception
	 * @return mixed
	 */
	public function log($level, $type, $msg) {
		if (is_array($msg)) $msg = @json_encode($msg);
		file_put_contents($this->_file, date("Y-m-d H:i:s")."\t$level\t$type\t$msg\n", FILE_APPEND);
	}

}