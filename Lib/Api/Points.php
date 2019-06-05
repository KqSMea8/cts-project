<?php
/**
 * Created by PhpStorm.
 * User: cts
 * Date: 2018/10/31
 * Time: 10:41 AM
 */
class Api_Points extends Api_Weibo{
    protected $connectTimeout = 1000;
    protected $timeout = 1000;

    protected $domain = 'http://luckypoints.sc.weibo.com/';
    const SOURCE = 1013;
    const KEY = '930e2f7f7793fe965415';


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
    function getUserStatus($uid){
        $data = array(
            'source'    => self::SOURCE,
            'uid'       => $uid,
            't'         => time(),
        );
        $data['sign'] = $this->getSign($data, self::KEY);
        return $this->post('/api/user/status', $data, 'json');
    }


    public function getRemind($uid)
    {
        $data = [
            'source' => self::SOURCE,
            'uid' => $uid,
        ];

        $data['sign'] = $this->getSign($data, self::KEY);

        return $this->get('/api/order/remind', $data, 'json');
    }
    public function getGoods($uid)
    {
        $data = [
            'source' => self::SOURCE,
            'uid' => $uid,
            't' => time(),
        ];

        $data['sign'] = $this->getSign($data, self::KEY);

        return $this->get('/api/recommendation/goods', $data, 'json');
    }

}