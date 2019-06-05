<?php

/**
 * 浏览器类型探针
 *
 * @copyright    (c) 2011, 新浪网 MiniBlog All rights reserved.
 * @author       wangying7 <wangyi7@staff.sina.com.cn>
 * @package      Swift
 */

class Lib_ClientProber {

    public static $user_agent = '';
    private static $user_agent_info_conf = array();
    private static $user_agent_info = array();

    /**
     * Returns information about the client user agent.
     *
     *     // Returns "Chrome" when using Google Chrome
     *     $browser = Request::user_agent('browser');
     *
     * Multiple values can be returned at once by using an array:
     *
     *     // Get the browser and platform with a single call
     *     $info = Kohana_Request::user_agent(array('browser', 'platform'));
     *
     * When using an array for the value, an associative array will be returned.
     *
     * @param   mixed   string to return: browser, version, robot, mobile, platform; or array of values
     * @return  mixed   requested information, FALSE if nothing is found
     */
    public static function get_client_agent($type = array('browser', 'browserversion', 'platform', 'mobile', 'pad', 'mobilephone')) {
        if (empty(self::$user_agent_info)) {
            self::_init_user_agent_info();
        }

        if (!is_array($type)) {
            if (isset(self::$user_agent_info[$type])) {
                return self::$user_agent_info[$type];
            } else {
                return false;
            }
        }

        return self::$user_agent_info;
    }

    private static function _init_user_agent_info() {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            self::$user_agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            return false;
        }

        if (empty(self::$user_agent_info_conf)) {
            $user_agent_conf = require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'clientprober' . DIRECTORY_SEPARATOR . 'user_agents.php';
            self::$user_agent_info_conf = $user_agent_conf;
        }

        //先获取浏览器信息
        self::get_browser_info();
        //获取操作系统信息
        self::get_other_info('platform');

        //判断是否来自移动端
        //首先判断是否是山寨机
        $is_wml = self::is_wml_only();
        if ($is_wml) {
            self::$user_agent_info['mobile'] = 'MTK';
            self::$user_agent_info['pad'] = false;
            self::$user_agent_info['mobilephone'] = 'MTK';
        } elseif (stripos(self::$user_agent,'android') !== false) {
            //安卓特殊处理,安卓3.x版本是pad专用
            self::$user_agent_info['mobile'] = 'Android';
            if (stripos(self::$user_agent,'android 3')) {
                self::$user_agent_info['pad'] = 'Android';
                self::$user_agent_info['mobilephone'] = false;
            } else {
                self::$user_agent_info['mobilephone'] = 'Android';
                self::$user_agent_info['pad'] = false;
            }
        } else {
            self::get_other_info('pad');
            if (self::$user_agent_info['pad']) {
                self::$user_agent_info['mobilephone'] = false;
                self::$user_agent_info['mobile'] = self::$user_agent_info['pad'];
            } else {
                $name = self::get_other_info('mobilephone');
                self::$user_agent_info['mobile'] = $name;
            }
        }
    }

    private static function get_other_info($type) {
        $group = self::$user_agent_info_conf[$type];
        foreach ($group as $search => $name) {
            if (stripos(self::$user_agent, $search) !== FALSE) {
                self::$user_agent_info[$type] = $name;
                return $name;
            }
        }
        self::$user_agent_info[$type] = false;
        return false;
    }

    private static function get_browser_info() {
        // Load browsers
        $browsers = self::$user_agent_info_conf['browser'];
        $ua = self::$user_agent;
        foreach ($browsers as $search => $name) {
            if (stripos($ua, $search) !== FALSE) {
                // Set the browser name
                self::$user_agent_info['browser'] = $name;
                if (preg_match('#'.preg_quote($search).'[^0-9.]*+([0-9.][0-9.a-z]*)#i', $ua, $matches)) {
                    // Set the version number
                    self::$user_agent_info['browserversion'] = $matches[1];
                } else {
                    // No version number found
                    self::$user_agent_info['browserversion'] = FALSE;
                }
                return true;
            }
        }
        self::$user_agent_info['browser'] = false;
        self::$user_agent_info['browserversion'] = false;
        return true;
    }

    //判断是否识别山寨机或低端手机只支持xhtml
    private static function is_wml_only() {
        $str = '#(text/vnd.wap.wml)|(application/vnd.wap.xhtml)|(image/vnd.wap.wbmp)#';
        $http_accept = $_SERVER['HTTP_ACCEPT'];
        if (!empty($http_accept)) {
            if (preg_match($str,$http_accept)) {
                return true;
            }
        }
        return false;
    }

}
