<?php
/**
 * Tool_Sms
 * 发送短信
 * @author    liuhui9<liuhui9@staff.sina.com.cn>
 * @created   2016-11-10
 * @copyright copyright(2016) weibo.com all rights reserved
 */
class Tool_Sms {

    private static $sms_account = array(
        'base' => array(
            'api' => "http://i.sms.weibo.cn/send",
            'srcid' => '1065752190711621',
            'from' => '106021',
        ),
    );

    /**
     *
     * @param string $destid 手机号码
     * */
    public static function sendSMS($destid, $msg, $sms_type = 'base') {
        $config = self::$sms_account[$sms_type];
        $from = $config['from'];
        $srcid = $config['srcid'];
        $api = $config['api'];

        $msg = iconv("UTF-8", "GBK//IGNORE", $msg);//最多560字节
        $msg = urlencode($msg);
        $timestamp = time();//时间戳验证五秒内有效
        $sign = self::getsign(
            array(
                'from'      => $from,
                'srcid'     => $srcid,
                'destid'    => $destid,
                'msg'       => $msg,
                'timestamp' => $timestamp,
            ),
            ''
        );
        //var_dump($sign);

        //外网用
        //$api = "http://api.sms.weibo.cn/send?from={$from}&srcid={$srcid}&destid={$destid}&msg={$msg}&timestamp={$timestamp}&sign={$sign}"; var_dump($sign);
        //仿真用
        $api = "{$api}?from={$from}&srcid={$srcid}&destid={$destid}&msg={$msg}";

        $params = array(
            'from'   => $from,
            'srcid'  => $srcid,
            'destid' => $destid,//手机号码
            'msg'    => $msg,
        );

        //$curl = new Lib_Curl();
        //$ret = $curl->get($api, $params);
        $ret = file_get_contents($api);
        return $ret;
    }

    private static function getsign(array $param, $secret)
    {
        //重新排序参数列表
        ksort($param);
        $str = '';
        //生成签名串
        foreach ($param as $k => $v) {
            // sign参数及参数值为空的参数不参加签名
            if ($k != 'debug' && $k != 'sign' && $v !== '') {
                $str .= $k . '=' . $v . '&';
            }
        }    //去掉签名串最后的& 在拼接上secret
        $str = substr($str, 0, -1) . $secret;
        //返回md5加密的签名
        return md5($str);
    }
}
