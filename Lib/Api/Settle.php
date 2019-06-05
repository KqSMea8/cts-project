<?php
/**
 * Created by PhpStorm.
 * User: cts
 * Date: 2018/9/6
 * Time: 下午4:02
 */
class Api_Settle extends Api_Weibo{
    protected $domain = 'http://admin.jinxiaocun.sc.weibo.com';
    const KEY = '867ea1d68b3a9854f116e';
    const SOURCE = "exchange";
    const EXCHANGE_TYPE = 2;
    const SIGN_TYPE = "md5";

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


    function orderInput($orderId,$orderTime,$userId,$score,$count,$goodsId,$phone,$address,$name) {
        $this->setDebug(true);
        $data = array(
            'source' => self::SOURCE,
            'sign_type' => self::SIGN_TYPE,
            'type' => self::EXCHANGE_TYPE,
            'order_id' => $orderId,
            'order_time' => $orderTime,
            'user_id' => $userId,
            'jifen' => $score,
            'count' => $count,
            'goods_id' => $goodsId,
            'express_phone' => $phone,
            'express_address' => $address,
            'express_name' => $name,
        );
        $data['sign'] = $this->getSign($data, self::KEY);
        return $this->post('/Api/Order/InputOrder', $data, 'json');
    }



}