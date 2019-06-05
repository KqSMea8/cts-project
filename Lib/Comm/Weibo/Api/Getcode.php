<?php
/**
 * 获取授权code接口
 * @author     Stephen <zhangdi3@staff.sina.com.cn>
 * @copyright  copyright(2011) weibo.com all rights reserved
 */
class Comm_Weibo_Api_Getcode 
{
    const GETCODE  = "http://i2.api.weibo.com/2/taccount/weibo/getcode.json";
    /**
     * 发送邀请
     */
    public static function getcode($uid, $source)
    {
        $request = new Comm_Weibo_Api_Request_Platform(self::GETCODE,'POST');
        $request->add_rule('source', 'string',TRUE);  
        $request->add_rule('uid', 'int64', TRUE); 
        $request->add_rule('sign', 'string', TRUE); 

        $arr = array(
            'uid'    => $uid,
            'source' => $source,
        );
        
        $signArr = self::sign($arr, self::GETCODE);
        $request->source = $source;
        $request->uid    = $uid;
        $request->sign   = $signArr['sign'];
        return $request;
    }

    public static function sign($params, $url) {
        $appSecret = '5cae45e2d58257b3da2f67397fd6e5f2';
        if ($pos = strpos($url, '?')) {
            $queryStr = substr($url, $pos + 1);
            parse_str($queryStr, $getParams);
            $url      = substr($url, 0, $pos);
            $params   += $getParams;
        }
        ksort($params);
        $params['sign'] = md5(base64_encode(urldecode(http_build_query($params))) . $appSecret);
        return $params;
    }
}