<?php

class Lib_Mobile {

    public static function come_from_mobile() {
        $user_agent_info = Lib_ClientProber::get_client_agent();
        return isset($user_agent_info['mobilephone']) && $user_agent_info['mobilephone'];
    }


    /**
     * 通过字符串的平台标示转化得到编号
     * @param string $platform_str
     * @return mixed
     */
    public static  function get_platform_id($platform_str = "") {
        if (empty($platform_str)) {
            $platform = self::come_from_mobile() ? "h5" : "pc";
        } else {
            $platform = $platform_str;
        }
        $platform = strtolower($platform);
        $platform_config = array(
            'all' => 0,      //所以平台不细分
            'pc'  => 10,     //cp端
            'mobile' => 20, //移动端
            'mo' => 21,      //移动端细分H5
            'h5' => 21,      //移动端细分H5
            'android' => 22,//移动端细分android
            'ios' => 23,     //移动端细分ios
        );
        $platform_id = isset($platform_config[$platform]) ? $platform_config[$platform] : $platform_config['all'];
        return $platform_id;
    }



}
