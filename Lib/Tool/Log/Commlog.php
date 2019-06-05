<?php

/**
* 通用日志类
*
* @package    request
* @copyright  copyright(2011) weibo.com all rights reserved
* @author     weibo.com php team
*/
class Tool_Log_Commlog {

	const LOG_DIR = '/commlog/'; //日志目录

	/**
	 * 记录日志
	 * 日志格式：类型|时间|客户ip|日志内容
	 */
	public static function write_log($type, $content) {

		if (!isset($_SERVER['SINASRV_APPLOGS_DIR']) || !is_dir($_SERVER['SINASRV_APPLOGS_DIR'])) {
			return;
		}

		$log_dir = $_SERVER['SINASRV_APPLOGS_DIR'] . self::LOG_DIR;
		if (!is_dir($log_dir)) {
			mkdir($log_dir);
			chmod($log_dir, 0777);
		}

		$viewer = Comm_Context::get('viewer', FALSE);
		$uid = 0;
		if (FALSE !== $viewer) {
			$uid = $viewer->id;
		}

		$stack = debug_backtrace(0);
		$var_file = $stack[1]['file'];
		$var_line = $stack[1]['line'];
		$log_file = $log_dir.''.date('Ymd').'.log';

		$log = array(
			'type'  => $type,
			'time'  => date("Y-m-d H:i:s"),
			'cip'   => 'client:' . Comm_Context::get_client_ip(),
			'dpool' => 'server:' . Comm_Context::get_server('SERVER_ADDR'),
			'uid'   => 'uid:' . $uid,
			'uri'   => 'uri:' . $_SERVER['REQUEST_URI']
		);
		if (in_array($type,array('WARNING','FATAL'))) {
			$log_file .= '.wf';
			$log['location'] = '@' . $var_file . '[' . $var_line . ']';
		}elseif (in_array($type,array('STATISTIC'))) {
			$log_file .= '.st';
			$log['location'] = '@' . $var_file . '[' . $var_line . ']';
            $log['content'] = 'msg:' . $content;
            $res = error_log(self::format_log($log) . "\n", 3, $log_file);
            if(!$res){//失败再尝试记录一次
                error_log(self::format_log($log) . "\n", 3, $log_file);
            }
            return;
        }else if($type == 'RE'){
			$log_file .= '.re';
			$log['location'] = '@' . $var_file . '[' . $var_line . ']';
            $log['content'] = 'msg:' . $content;
            $res = error_log(self::format_log($log) . "\n", 3, $log_file);
            if(!$res){//失败再尝试记录一次
                error_log(self::format_log($log) . "\n", 3, $log_file);
            }
            return;
        }

		$log['content'] = 'msg:' . $content;
		error_log(self::format_log($log) . "\n", 3, $log_file);
		return;
	}

	public static function format_log($log = array()) {
		return join("\t", $log);
	}
}
