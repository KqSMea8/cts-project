<?php
/**
 * 微博业务层工具包
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author weibo.com php team
 * @package LIBRARIES
 */
class Comm_Weibo_Util {
    
    /**
     * 通过图片pid获取图片路径
     * @param string $picid 图片id号
     * @param string $pictype 图片类型（缩略图：thumbnail,原图:orignal)
     * @return string 图片路径
     */
    const PHOTO_URL_CRC = "http://ww%d.sinaimg.cn/%s/%s.%s";
    const PHOTO_URL = "http://ss%d.sinaimg.cn/%s/%s&690";
    public function get_pic_url($picid, $pictype = "thumbnail") {
        if (!is_array($picid))
            $picid = array($picid);
        $result = array();
        foreach ($picid as $pid) {
            if (empty($pid)) {
                throw new Comm_Exception_Program("pid error");
            }
            if ($pid[9] == 'w') {
                // 新系统显示规则，注意新系统用的crc32做的域名哈希  orignal
                if ($pictype == "orignal") {
                    $pictype = "large";
                }
                $hv = sprintf("%u", crc32($pid));
                $zone = fmod(floatval($hv), 4) + 1;
                $ext = ($pid[21] == 'g' ? 'gif' : 'jpg');
                $result[$pid] = sprintf(self::PHOTO_URL_CRC, $zone, $pictype, $pid, $ext);
            } else {
                $num = (hexdec(substr($pid, -2)) % 16) + 1;
                $result[$pid] = sprintf(self::PHOTO_URL, $num, $pictype, $pid);
            }
        }
        return $result;
    }
    
    /**
     * 根据用户浏览器获取特殊的css标识
     * @return int
     */
    const LOWER_IE_BROWER = 1;
    const HANDLE_MAC_EQUIPMENT = 2;
    public static function detect_special_css() {
        $info = Comm_ClientProber::get_client_agent();
        //       var_dump($_SERVER['HTTP_USER_AGENT']);
        //       echo '<br/>';
        //       var_dump($info);
        $show_special_css = FALSE;
        if (isset($info['browser']) && $info['browser'] && $info['browser'] == 'Internet Explorer') {
            if (!strpos(Comm_ClientProber::$user_agent, "MSIE 9.0") && !strpos(Comm_ClientProber::$user_agent, "MSIE 8.0") && !strpos(Comm_ClientProber::$user_agent, "MSIE 7.0")) {
                $show_special_css = self::LOWER_IE_BROWER;
            }
        
        }
        if (isset($info['mobile']) && $info['mobile'] && isset($info['platform']) && $info['platform'] && $info['platform'] == 'Mac OS X') {
            $show_special_css = self::HANDLE_MAC_EQUIPMENT;
        }
        return $show_special_css;
    }
    
    /**
     * 检查$_SERVER['WEIBO_ENV']变量
     * 根据环境变量决定是否显示调试信息
     * 
     */
    public static function weibo_env_check() {
        // 环境检查，确保变量
        if (!isset($_SERVER['WEIBO_ENV']) || empty($_SERVER['WEIBO_ENV'])) {
            die("there is not \$_SERVER['WEIBO_ENV'] variable!");
        }
        if (in_array($_SERVER['WEIBO_ENV'], array('pro', 'int'))) {
            if (true == ini_get('display_errors')) {
                die("disable display_errors please! current env: " . $_SERVER['WEIBO_ENV']);
            }
            $show_trace_info = false;
        } elseif (in_array($_SERVER['WEIBO_ENV'], array('test', 'dev'))) {
            if (false == ini_get('display_errors')) {
                die("enable display_errors please! current env: " . $_SERVER['WEIBO_ENV']);
            }
            $show_trace_info = true;
        } else {
            die("invalid \$_SERVER['WEIBO_ENV'] value: " . $_SERVER['WEIBO_ENV']);
        }
        
        return $show_trace_info;
    }
}