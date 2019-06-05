<?php
/*
 * *************************************************
 * Created on :2012-8-6 9:40:42
 * Encoding   :UTF-8
 * Description:
 *
 * @Author @小差小差 <qingshou@staff.sina.com.cn>
 * @WAP_WEIBO (C)1996-2099 SINA Inc.
 * ************************************************
 */

class Sso_Sdk_Cookie_Gsid {

    private static $version = 1; //这个gsid生成算法的version
    private static $string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const SECRET_LENGTH = 12;   //随机串的长度
    const IEME_SIGN_LENGTH = 4;

    /**
     * 用于解析gsid
     * @param string $gsid
     * @return boolean | array
     */
    public static function parse($gsid) {
        if (!self::checkSign($gsid)) return false;
        $results = array();
        $tokenheader = self::decodeBx(substr($gsid, 0, 2));
        //版本不匹配
        if (self::$version != ($tokenheader >> 8)) return false;
        $uidlen = $tokenheader & 15;
        $results['idc'] = ($tokenheader & 240) >> 4;//掩码二进制为11110000
        $results['uid'] = self::decodeUid(substr($gsid, 0 - $uidlen));
        $results['iemesign'] = substr($gsid, 4, self::IEME_SIGN_LENGTH);
        $projectlen = strlen($gsid) - 2 - 2 - self::IEME_SIGN_LENGTH - $uidlen - self::SECRET_LENGTH;
        $results['project'] = self::decodeBx(substr($gsid, 4 + self::IEME_SIGN_LENGTH, $projectlen));
        $results['secret'] = substr($gsid, 4 + self::IEME_SIGN_LENGTH + $projectlen, self::SECRET_LENGTH);
        return $results;
    }
    /**
     * 对str取签名，两位
     * @param string $str
     * @return string
     */
    private static function getSignature($str = '') {
        $forlong = "mADcPY2rxM7x";

        if (PHP_INT_MAX > 2147483647) {
            return str_pad(substr(self::encodeBx(crc32($str . $forlong)), -2), 2, '0');
        } else {
            $crc32_value = floatval(sprintf("%u", crc32($str . $forlong)));
            if ($crc32_value > 2147483647.00) {
                $x = strlen(self::$string);
                $out = '';
                $s = (int) ($crc32_value - 2147483647.00);
                $d = ($s % $x) + (2147483647 % $x);
                $r = floor($s / $x) + floor(2147483647 / $x);

                if ($d >= $x) {
                    $r++;
                    $d = $d - $x;
                }
                $out = substr(self::$string, $d, 1) . $out;
                while ($r > 0) {
                    $idx = $r % $x;
                    $out = substr(self::$string, $idx, 1) . $out;
                    $r = floor($r / $x);
                }
                return str_pad(substr($out, -2), 2, '0');

            }
            return str_pad(substr(self::encodeBx((int)$crc32_value), -2), 2, '0');
        }
    }

    /**
     * 将62进制编码的uid还原
     * @param string $uid
     * @return string
     */
    private static function decodeUid($uid) {
        return self::decodeBx(substr($uid, 0, -2)) . str_pad(self::decodeBx(substr($uid, -2)), 3, "0", STR_PAD_LEFT);
    }

    /**
     * 检查gsid的签名位是否正确
     * @param string $gsid
     * @return boolean
     */
    private static function checkSign($gsid) {
        if ($signStr = self::getSignature(substr($gsid, 4))) {
            if (substr($gsid, 2, 2) == $signStr) {
                return true;
            }
        }
        return false;
    }


    /**
     * 将10进制转换成64进制
     *
     * @param	string	$str	10进制字符串
     * @return	string
     */
    public static function encodeBx($str) {
        $x = strlen(self::$string);
        $out = '';
        while ($str > 0) {
            $idx = $str % $x;
            $out = substr(self::$string, $idx, 1) . $out;
            $str = floor($str / $x);
        }
        return $out;
    }

    /**
     * 将62进制转换成10进制
     *
     * @param	string	$str	64进制字符串
     * @return	string
     */
    public static function decodeBx($str) {
        $x = strlen(self::$string);
        $out = 0;
        $base = 1;
        for ($t = strlen($str) - 1; $t >= 0; $t-=1) {
            $out = $out + $base * strpos(self::$string, substr($str, $t, 1));
            $base *= $x;
        }
        return $out . "";
    }
}
