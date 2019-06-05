<?php

/*
 * 日志工具
 * 
 * TODO: 日志格式需要重新设计
 */

class Lib_Log
{
    const LOG_DIR = '/commlog/'; //日志目录

    public static function debug($msg)
    {
        self::writeLog('DEBUG', $msg);
    }

    public static function info($msg)
    {
        self::writeLog('INFO', $msg);
    }

    public static function notice($msg)
    {
        self::writeLog('NOTICE', $msg);
    }

    public static function warning($msg)
    {
        self::writeLog('WARNING', $msg);
    }

    public static function fatal($msg)
    {
        self::writeLog('FATAL', $msg);
    }

    public static function stat($msg)
    {
        self::writeLog('STAT', $msg);
    }

    public static function io($msg)
    {
        self::writeLog('io', $msg);
    }

    public static function exception(Exception $e){
        self::warning($e->getFile() . ":" . $e->getLine() . "#" . $e->getMessage());
    }
    /**
     * 记录日志
     * 日志格式：类型|时间|客户ip|日志内容
     */
    public static function writeLog($type, $content,$suffix = '')
    {
        if (!isset($_SERVER['SINASRV_APPLOGS_DIR']) || !is_dir($_SERVER['SINASRV_APPLOGS_DIR'])) {
            return;
        }

        $log_dir = $_SERVER['SINASRV_APPLOGS_DIR'] . self::LOG_DIR;
        if (!is_dir($log_dir)) {
            mkdir($log_dir);
            chmod($log_dir, 0777);
        }

        $uid = 0;

        $stack = debug_backtrace(0);
        $var_file = $stack[1]['file'];
        $var_line = $stack[1]['line'];
        $log_file = $log_dir . '' . date('Ymd') . '.log';

        $log = array(
            'type' => $type,
            'time' => date("Y-m-d H:i:s"),
            'cip' => 'client:' . $_SERVER['REMOTE_ADDR'],
            'dpool' => 'server:' . $_SERVER['SERVER_ADDR'],
            'uid' => 'uid:' . $uid,
            'uri' => 'uri:' . $_SERVER['REQUEST_URI']
        );
        if (in_array($type, array('WARNING', 'FATAL'))) {
            $log_file .= '.wf';
            $log['location'] = '@' . $var_file . '[' . $var_line . ']';
        } elseif (in_array($type, array('STATISTIC'))) {
            $log_file .= '.st';
            $res = error_log(self::formatLog(array('content' => $content)) . "\n", 3, $log_file);
            if (!$res) {//失败再尝试记录一次
                error_log(self::formatLog(array('content' => $content)) . "\n", 3, $log_file);
            }
            return;
        } else if ($type == 'RE') {
            $log_file .= '.re';
            $log['location'] = '@' . $var_file . '[' . $var_line . ']';
            $log['content'] = 'msg:' . $content;
            $res = error_log(self::formatLog($log) . "\n", 3, $log_file);
            if (!$res) {//失败再尝试记录一次
                error_log(self::formatLog($log) . "\n", 3, $log_file);
            }
            return;
        } else if ($type == 'process') {
            $log_file .= '.proc.'.$suffix;
            $log = array();
            $log['time'] = date("Y-m-d H:i:s");
            $log['location'] = '@' . $var_file . '[' . $var_line . ']';
            $log['content'] = 'msg:' . $content;
            $res = error_log(self::formatLog($log) . "\n", 3, $log_file);
            if (!$res) {//失败再尝试记录一次
                error_log(self::formatLog($log) . "\n", 3, $log_file);
            }
            return;
        }
        if ($type == "SQL") {
            $log_file .= '.sql';
            $log = array();
            $log['request_url'] = Yaf_Registry::get("request_url");
            $log['request_id'] = Yaf_Registry::get("request_id");
        }
        $log['content'] = 'msg:' . $content;

        if ($type == "io") {
            $log_file .= '.io';
            $log = array();
            $log['content'] = $content;
        }

        error_log(self::formatLog($log) . "\n", 3, $log_file);
        return;
    }

    public static function formatLog($log = array())
    {
        return join("\t", $log);
    }


    public static function warningstr($pre = '', $data = array())
    {
        $str = self::arr2str($data);
        self::warning($pre . $str);
    }

    public static function infostr($pre = '', $data = array())
    {
        $str = self::arr2str($data);
        self::info($pre . $str);
    }



    /**
     *输出日志记录
     * @param string $request_uri
     * @param int $request_id
     * @param array $data
     * @param int $exec_time
     * @param bool $debug
     */
    public static function output($request_uri = '', $request_id = 0, $data = array(), $exec_time = 0, $debug = false)
    {
        $core = isset($data['code']) ? $data['code'] : 'null';
        $msg = isset($data['msg']) ? $data['msg'] : 'null';
        $data_unset = array('code', 'msg', 'request_id');
        foreach ($data_unset as $v) {
            if (isset($data[$v])) {
                unset($data[$v]);
            }
        }
        $data_not_empty = empty($data['data']) ? 0 : count($data['data']);
        $str = self::arr2str($data);
        //日志格式不要随意修改，否则可能导致dip数据解析不正常(shengfu 2016年2月24日17:21:48)
        $time = time();
        $uid = Yaf_Registry::get("request_uid");
        $uid = empty($uid) ? 0 : intval($uid);
        $log = array(
            'type' => 'output',
            'uid' => $uid,
            'time' => date("Y-m-d H:i:s", $time),
            'cip' => !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0',
            'dpool' => !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0',
            'request_id' => $request_id,
            'request_uri' => !empty($request_uri) ? $request_uri : 'null',
            'exec_time' => $exec_time,
            'core' => empty($core) ? 0 : $core,
            'msg' => empty($msg) ? 'null' : $msg,
            'data_not_empty' => $data_not_empty,

        );
        $log['data'] = $data_not_empty > 0 && $debug ? self::arr2str($data) : 'null';

        self::io(implode("\t", $log));
    }

    public static function arr2str($arr)
    {
        try {
            if (is_null($arr)) {
                return "null";
            }
            if (is_string($arr) || is_numeric($arr)) {
                return $arr;
            }
            if ($arr === true) {
                return 'true';
            }
            if ($arr === false) {
                return 'false';
            }
            if (is_object($arr)) {
                return "object";
            }
            if (!is_array($arr)) {
                return "unrecognized";
            }
            $ret = "[";
            foreach ($arr as $k => $v) {
                $ret .= $k . "=>" . self::arr2str($v) . ",";
            }
            $ret = rtrim($ret, ",");
            $ret .= "]";
            return $ret;
        } catch (Exception $e) {
            return "Exception";
        }
    }
}
