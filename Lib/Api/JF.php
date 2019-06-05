<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2018/12/19
 * Time: 9:57 AM
 */
class Api_JF extends Api_Abstract {
    protected $connectTimeout = 3000;
    protected $timeout = 3000;

    protected $domain = 'http://jf.sc.weibo.com';
    private $privateKeyPath = '';
    protected $mid = '';



    public function init() {
//        if (ENVIRONMENT == 'pro') {
//            $this->domain = 'http://api.task.weibo.com/';
//        } else {
//            $this->domain = 'http://test.api.task.weibo.com/';
//        }
    }

    protected function getSign(array $param) {
        // -- Get a PEM formatted private key
        switch ($this->mid){
            case Model_JF::MID_DH:
                $this->privateKeyPath = PATH_CONFIG . '/dh_private_key.pem';
                break;
            case Model_JF::MID_DXJ:
                $this->privateKeyPath = PATH_CONFIG . '/dxj_private_key.pem';
                break;
            case Model_JF::MID_DEV:
                $this->privateKeyPath = PATH_CONFIG . '/jf_private_key.pem';
                break;
            default:
                return '';
        }

        $privateKey = file_get_contents($this->privateKeyPath);

        ksort($param);
        reset($param);
        foreach ($param as $k=>$v) {
            if (is_null($v)) {
                unset($param[$k]);
            }
            if ('' === $v) {
                unset($param[$k]);
            }
            if ($k == "sign" || $k == "sign_type") {
                unset($param[$k]);
            }
        }
        $pairs = array();
        foreach ($param as $k=>$v) {
            $pairs[] = "$k=$v";
        }
        $sign_data = implode('&', $pairs);
        // -- Returns a positive key resource identifier on success, or FALSE on error
        // -- the key maybe the format file://path/to/file.pem and MUST NAMED PEM. or a PEM formatted private key.
        $id = openssl_pkey_get_private($privateKey);

        // -- computes a signature for the specified data by using SHA1 for hashing followed by encryption using the private key associated with priv_key_id.
        // -- Note that the data itself is not encrypted.
        openssl_sign($sign_data, $signature, $id);

        // -- frees the key associated with the specified key_identifier from memory
        openssl_free_key($id);

        // -- base64 encode the signature
        return base64_encode($signature);
    }
    protected function setMid($mid){
        if(ENVIRONMENT == 'pro') {
            $this->mid = $mid;
        }else{
            $this->mid = Model_JF::MID_DEV;
        }
    }
    public function getUserScore($mid, $uid){
        $this->setMid($mid);
        $params = array(
            'mid'       =>$this->mid,
            'uid'       =>$uid,
            'sign_type' =>'rsa',
        );
        $params['sign'] = $this->getSign($params);
        return $this->post('/api/comm/user/jf', $params, self::RETURN_TYPE_JSON);

    }
    public function consume($mid, $uid, $orderId, $jf, $subject, $type = 2){
        $this->setMid($mid);
        $params = array(
            'sign_type'         =>'rsa',
            'type'              =>$type,
            'out_apply_id'      =>$orderId,
            'buyer_uid'         =>$uid,
            'seller_uid'        =>$this->mid,
            'jf'                =>$jf,
            'subject'           =>$subject,
        );
        $params['sign'] = $this->getSign($params);
        return $this->post('/api/consume/operate', $params, self::RETURN_TYPE_JSON);
    }
    public function consumeQuery($mid, $orderId){
        $this->setMid($mid);
        $params = array(
            'sign_type'         =>'rsa',
            'out_apply_id'      =>$orderId,
            'seller_uid'        =>$this->mid,
        );
        $params['sign'] = $this->getSign($params);
        return $this->post('/api/consume/query', $params, self::RETURN_TYPE_JSON);
    }
    public function verifyApply($mid, $orderId, $jf, $subject){
        $this->setMid($mid);
        $params = array(
            'sign_type'         => 'rsa',
            'uid'               => $this->mid,
            'verify_jf'         => $jf,
            'subject'           => $subject,
            'out_apply_id'      => $orderId,

        );
        $params['sign'] = $this->getSign($params);
        return $this->post('/api/comm/verify/apply', $params, self::RETURN_TYPE_JSON);
    }
}