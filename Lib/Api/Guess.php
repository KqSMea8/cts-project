<?php
/**
 * Created by PhpStorm.
 * User: cts
 * Date: 2018/9/6
 * Time: 下午4:02
 */
class Api_Guess extends Api_Weibo{
    protected $domain = 'http://luckyguess.sc.weibo.com';
    const KEY = '930e2f7f7793fe965415';
    const SOURCE = "1012";

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
    function getOrderRemind($uid){
        $this->setDebug(true);
        $data = array(
            'source' => self::SOURCE,
            'uid' => $uid,
        );
        $data['sign'] = $this->getSign($data, self::KEY);
        return $this->post('/api/order/remind', $data, 'json');
    }

    public function getTopics($uid)
    {

        $data = [
            'source' => self::SOURCE,
            'uid' => $uid,
        ];

        $data['sign'] = $this->getSign($data, self::KEY);

        return $this->get('/api/recommendation/topics', $data, 'json');
    }

}