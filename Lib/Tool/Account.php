<?php
/**
 * 查询用户认证类型
 * 
 * @author      Stephen<zhangdi3@staff.sina.com.cn>
 * @version 	2014-06-18
 * @copyright  	copyright(2014) weibo.com all rights reserved
 *
 */
class Tool_Account
{    
    CONST SSOURL = "http://ilogin.sina.com.cn/api/getsso.php";
    CONST KEY    = "442fe76d944840ac07fcfa4b2e3a1668";
    
    public static function checkverifytype($uid) 
    {        
        $userinfo = Dr_User::get_user_info($uid);
        if ($userinfo->verified) 
        {
            if ($userinfo->verified_type == 0) 
            {
                $type = "yellow";
            }
            else
            {
                $type = "blue";
            }
        }
        else 
        {
            $type = "none";
        }
        return $type;
    }
    
    /**
     * 获取用户登录账号
     * 接口参数说明 (粗体字为必填参数)
        entry
        项目标识 (由统一注册颁发)
        user
        用户登入ID / 用户唯一号
        m
        校验值： m = md5( user +  pin) ; 其中pin为统一注册给每个项目颁发的密钥
        caller
        调用该接口的文件的绝对路径，用于接口变动时联系使用方
        needlastlogin
        是否需要最后登录时间、ip信息，可选值：
            0： 不需要 【默认值】
            1： 需要
        needlogincity
        是否需要常用登录地信息，可选值：
            0： 不需要 【默认值】
            1： 需要
     * 
     */
    public static function getusername($uid)
    {
        $request = array();
        $request['entry']  = "payadmin";
        $request['user']   = $uid;
        $request['m']      = md5($uid.self::KEY);
        $request['caller'] = urlencode("http://mall.sc.weibo.com/payapply");
        #echo '<pre>';print_r($request);exit;
        $result = self::send(self::SSOURL, $request,true);
        #$result = "result=succ&uniqueid=3314144323&appgroup=8&userid=hdtest02.cn&regname=hdtest02%40sina.cn&name=hdtest02%40sina.cn&st=0&ag=8";
        $resultarr = explode("&",$result);
        $res = array();
        foreach ($resultarr as $val)
        {
            $vals = explode("=", $val);
            $res[$vals[0]] = $vals[1]; 
        }
        if ($res['result'] == "succ") 
        {
            return $res['name'];
        }
        else
        {
            return false;
        } 
        #echo '<pre>';print_r($request);exit;
    }
    
    private static function send($url,$param,$is_post = false,$connecttimeout=3,$timeout=3)
    {
        $data = array(
                'url'            => $url,
                'is_post'        => $is_post,
                'data'           => $param,
                'connecttimeout' => $connecttimeout,
                'timeout'        => $timeout,
        );
        return Tool_Curl::request($data);
    }
}