<?php
/**
 * Created by PhpStorm.
 * User: cts
 * Date: 2018/9/6
 * Time: 下午4:02
 */
class Api_Luck extends Api_Weibo{
    protected $domain = 'http://luck.sc.weibo.com';
    const KEY = '867ea1d68b3a9854f116e';
    const SOURCE = "exchange";

    protected function getSign($arrPara, $key) {

        ksort($arrPara);
        reset($arrPara);
        $paraFilter = [];
        foreach ($arrPara as $k => $v) {
            if ($k == "sign" || $k == "sign_type" || $v === "" || is_null($v)) {
                continue;
            }
            $paraFilter[$k] = $arrPara[$k];
        }
        $pairs = [];
        foreach ($paraFilter as $k => $v) {
            $pairs[] = "$k=$v";
        }
        return md5(implode('&', $pairs) . $key);
    }
    function getDuibaAutoLogin($uid){
        $data = array(
            'source' => self::SOURCE,
            'sign_type' => 'md5',
            'uid' => $uid,
        );
        $data['sign'] = $this->getSign($data, self::KEY);
        return $this->post('/api/duiba/autologin', $data, 'json');
    }

    function accountLog($uid,$sellerUid,$gold,$createTime) {
        $data = array(
            'source' => self::SOURCE,
            'sign_type' => 'md5',
            'uid' => $uid,
            'seller_uid' => $sellerUid,
            'gold' => $gold,
            'create_time' => $createTime,
        );
        $data['sign'] = $this->getSign($data, self::KEY);
        return $this->post('/api/account/log', $data, 'json');
    }



}