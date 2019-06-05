<?php
class Tool_Log extends Tool_Log_Commlog {

    public static function debug($msg) {
        self::write_log('DEBUG', $msg);
    }

    public static function info($msg) {
        self::write_log('INFO', $msg);
    }

    public static function notice($msg) {
        self::write_log('NOTICE', $msg);
    }

    public static function warning($msg) {
        self::write_log('WARNING', $msg);
    }

    public static function fatal($msg) {
        self::write_log('FATAL', $msg);
    }

    public static function statistic($msg) {
        self::write_log('STATISTIC', $msg);
    }

    public static function re($msg) {//红包日志
        self::write_log('RE', $msg);
    }
}
