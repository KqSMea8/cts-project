<?php


class Model_User
{
    /**
     * 格式化用户信息至精简版
     *
     * @param array $userInfo 用户对象为openapi返回的标准格式
     * @see http://wiki.intra.weibo.com/1/user
     * @return array
     */
    public static function formatLiteUserInfo(array $userInfo) {
        $data = array();
        $data['id'] = $userInfo['id'];
        $data['screen_name'] = $userInfo['screen_name'] != '' ? $userInfo['screen_name'] : $userInfo['id'];
        $data['name'] = $userInfo['name'] != '' ? $userInfo['name'] : $userInfo['id'] ;
        $data['profile_image_url'] = $userInfo['profile_image_url'];
        $data['domain'] = $userInfo['domain'] != '' ? $userInfo['domain'] : $userInfo['id'];
        $data['gender'] = $userInfo['gender'];
        $data['verified'] = $userInfo['verified'];
        $data['verified_type'] = $userInfo['verified_type'];
        $data['level'] = isset($userInfo['level']) ? $userInfo['level'] : 0;
        $data['badge'] = isset($userInfo['badge']) ? $userInfo['badge'] : NULL;
        $data['type'] = isset($userInfo['type']) ? $userInfo['type'] : NULL;
        return $data;
    }

    public static function getUser(){

        $info = array(
            'name' => 'cts'
        );

        return $info;

    }
}