<?php
class Lib_Ip {
    public static $tool_m_ip = '114.140.101.0'; //模拟海外ip
    public static $abroadTestUids = array("1827954005",'2122711027','2385617033','2102079770','2360577653','1918268027','1918273483','2052921001','2052967115','1770906217','1784322703');

    /**
     * 内网限制ip访问
     * @param string $ip 访问者的ip
     * @return boolean true/false
     */
    public static function allow_internal_ip($ip='') {
        $allow_internal_ip = array('/^10\.2[0-2].+/','/^121\.8\.156\.66/','/220\.248\.2\.98/','/^121\.8\.156\.74/','/^210\.5\.145\.58/','/^116\.236\.242\.198/','/219\.142\.118\.227/','/202\.106\.169\.241/','/202\.205\.3\.170/','/^61\.135\.152.+/','/^202\.106\.169.+/','/^219\.142\.118.+/');
        if ($ip == "") return false;
        @reset($allow_ip_webim);
        while(list($k,$v) = each($allow_internal_ip)) {
            if(preg_match($v,$ip)) return true;
        }
        return false;
    }

    /**
     * 获取国外IP
     * @param string $ip  访问者的ip
     * @return boolean true/false  (true 国外)
     */
    public static function checkAbroadIP($ip='') {
        if(in_array(Comm_Context::get('viewer')->id,self::$abroadTestUids)){
            return true;
        }
        if(empty($ip)) {
            $ip = Comm_Context::get_client_ip();
            if(empty($ip)){
                return false;
            }
        }
        if(is_numeric($ip)){
            $ip = long2ip($ip);
        }
        $ipArr = explode('.',$ip);
        $ipArr[3] = 0;
        $ip = implode('.',$ipArr);
        $ip = sprintf("%u", ip2long($ip));
        $index = sprintf("%03d", intval(fmod(crc32($ip), 512)));
        $filename = Comm_Config::get("env.privdata_dir_referer").'foreign_ip_conf/ip_'.$index.'.php';
        if(!is_file($filename)) {
            return false;
        }
        include_once($filename);
        if(isset($GLOBALS['FOREIGN_IP'][$ip])) {
            return true;
        }
        return false;
    }

    /**
     * 获取IP
     * @return unknown
     */
    static  public function getIp() {
        // Gets the default ip sent by the user
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $step = 1;
            $direct_ip = $_SERVER['REMOTE_ADDR'];
        }

        // Gets the proxy ip sent by the user
        $proxy_ip     = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $step = 2;
            $proxy_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $step = 3;
            $proxy_ip = $_SERVER['HTTP_X_FORWARDED'];
        } else if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $step = 4;
            $proxy_ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (!empty($_SERVER['HTTP_FORWARDED'])) {
            $step = 5;
            $proxy_ip = $_SERVER['HTTP_FORWARDED'];
        } else if (!empty($_SERVER['HTTP_VIA'])) {
            $step = 6;
            $proxy_ip = $_SERVER['HTTP_VIA'];
        } else if (!empty($_SERVER['HTTP_X_COMING_FROM'])) {
            $step = 7;
            $proxy_ip = $_SERVER['HTTP_X_COMING_FROM'];
        } else if (!empty($_SERVER['HTTP_COMING_FROM'])) {
            $step = 8;
            $proxy_ip = $_SERVER['HTTP_COMING_FROM'];
        }

        // Returns the true IP if it has been found, else FALSE
        if (empty($proxy_ip)) {
            // True IP without proxy
            $ip = $direct_ip;
        } else {
            $is_ip = preg_match('|^([0-9]{1,3}\.){3,3}[0-9]{1,3}|', $proxy_ip, $regs);
            if ($is_ip && (count($regs) > 0)) {
                // True IP behind a proxy
                $ip = $regs[0];
            } else {
                // Can't define IP: there is a proxy but we don't have
                // information about the true IP
                $ip = $direct_ip;
            }
        }
        return $ip;
    }

}