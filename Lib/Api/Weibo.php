<?php
/**
 * @copyright:copyright(2016) weibo.com all rights reserved
 * 平台接口调用统一封装
 *
 */
class Api_Weibo extends Api_Abstract
{
    protected $domain = "http://i2.api.weibo.com/2";
    protected $connectTimeout = 3000;
    protected $timeout = 3000;
    protected $authType = '';  //source_key,source,header,cookier,param,basic
    protected $authData = array();


    /**
     *设置橱窗app_token
     */
    public function setAppToken() {
        $appKey   = Model_Const::APP_SOURCE_EXCHANGE;
        $ret = Model_Cache::getAppToken($appKey);
        //基于PlatformAppToken认证
        if (isset($ret['token'])) {
            $this->setAuth('header', array('PlatformAppToken' => $ret['token']));
        }
        return $this;
    }

    /**
     * 此函数不允许修改，仅仅用于更新token脚本从平台接口获取原始token用，无缓存，大量调用会被封杀掉
     * 具体业务获取token自己发封装类似getAppToken函数
     * @param $app_key
     * @param $ips
     * @param int $ttl
     * @return mixed|string
     */
    public function queryAppToken($app_key, $ips, $ttl = 172800) {
        $this->setDomain("http://i.api.weibo.com");
        $data = array(
            'source' => $app_key,
            'ips' => $ips,
            'ttl' => $ttl, //默认ttl（可选项，默认为172800）token的生存时间，最长48小时。时间单位秒
        );
        return $this->get('/appverify/access_token.json', $data, self::RETURN_TYPE_JSON);
    }

    public function getTAuth($source){

        $this->setDomain("http://i2.api.weibo.com");
        $data = array(
            'source' => $source,
            'ips' => '10.0-255',
            'ttl' => 172800, //默认ttl（可选项，默认为172800）token的生存时间，最长48小时。时间单位秒
        );
        $ret = $this->post('/tauth2/access_token.json', $data, self::RETURN_TYPE_JSON);
        if (isset($ret['error_code'])) {
            Lib_Log::warning("|CRON TAUTH|GET TOKEN ERROR|" . json_encode($ret));
            return false;
        }
        return $ret;

    }
    protected function setTAuth($uid=0){
        //$this->setDebug(true);
        $tAuth = Model_Cache::getAppToken(Model_Const::APP_SOURCE_EXCHANGE);
        if (empty($uid)) {
            $uid = Lib_Config::get('Env.official_uid');
        }
        $uidStr = "uid={$uid}";
        $sign = urlencode(base64_encode(hash_hmac('sha1', $uidStr, $tAuth['tauth_token_secret'],true)));
        $tAuthStr = "TAuth2 token=\"".urlencode($tAuth['tauth_token'])."\",param=\"".urlencode($uidStr)."\",sign=\"$sign\"";
        $this->curl->headers['Authorization'] = $tAuthStr;
        return $this;
    }

    public function userShow($uid, $screen_name='', $returnType = self::RETURN_TYPE_JSON){
        if(empty($uid)) {
            $data = array(
                'screen_name' => $screen_name,
                'source' => Model_Const::APP_SOURCE_EXCHANGE,
            );
        }else{
            $data = array(
                'uid' => $uid,
                'source' => Model_Const::APP_SOURCE_EXCHANGE,
            );
        }
        return $this->setTAuth()->get('/users/show.json', $data, $returnType);
    }


    public function getUserDeliver($uid, $returnType = self::RETURN_TYPE_JSON){
        /*
        *Jumei_AddressController 已测
       */
        $data = array(
            'uid'=>$uid,
            'source'=>Model_Const::APP_SOURCE_EXCHANGE,
        );
        return $this->setTAuth()->get('/account/deliver_address.json', $data, $returnType);
    }


    public function getStatusesShow($mid, $returnType = self::RETURN_TYPE_JSON){
        $data = array(
            'id'=>$mid,
            'source'=>Model_Const::APP_SOURCE_EXCHANGE,
        );
        return $this->setTAuth()->get('/statuses/show.json', $data, $returnType);
    }


    public function getEmotions($type,$language, $returnType = self::RETURN_TYPE_JSON){
        $data = array(
            'type' => $type,
            'language' => $language,
            'source'=>Model_Const::APP_SOURCE_EXCHANGE,
        );
        return $this->setTAuth()->get('/emotions.json', $data, $returnType);
    }

    public function statusesUpdate($uid, $status, $returnType = self::RETURN_TYPE_JSON){
        $data = array(
            'status'=>$status,
            'source'=>Model_Const::APP_SOURCE_EXCHANGE,
        );
        return $this->setTAuth($uid)->post('/statuses/update.json', $data, $returnType);
    }
    public function epsTemplateSend($uid, $receiverId, $templateId, $params, $topColor, $note, $url, $linkDisplayName, $returnType = self::RETURN_TYPE_JSON){
        $data = array(
            'uid'               => $uid,
            'receiver_id'       => $receiverId,
            'template_id'       => $templateId,
            'data'              => json_encode($params),
            'topcolor'          => $topColor,
            'note'              => $note,
            'url'               => $url,
            'link_display_name' => $linkDisplayName,
        );
        return $this->setTAuth()->get('/eps/i_template/send.json', $data, $returnType);

    }

    public function messageDirectnew($senderUid, $uid, $content) {
        if(empty($content)){
            return false;
        }
        $sender = Lib_Config::get('message.' . $senderUid);
        if(!isset($sender)){
            return false;
        }
        $this->setDomain("http://i.e.weibo.com");

        $this->setDebug(true);

        $param = array();
        $param['identity'] = $sender['identity'];
        $param['send_uid'] = $senderUid;
        $param['recv_uid'] = $uid;
        $param['text'] = urlencode($content);
        $param['ts'] = time();

        $_prepare_skey = $param['identity'] . $param['ts'];
        $param['s'] = hash_hmac('sha1', $_prepare_skey, $sender['key']);

        return $this->post('/v1/api/message/directnew', $param, self::RETURN_TYPE_JSON);
    }


    public function create_friendships($uid, $firmUid, $returnType = 'json'){
        /*
        *  info:关注某用户
       */
        $data = array (
            "source" => Model_Const::APP_SOURCE_EXCHANGE,
            "uid"    => $firmUid,
        );
        return $this->setTAuth($uid)->post('/friendships/create.json', $data, $returnType);

    }

    public function getJsVersion($type, $returnType = 'json'){
        $data = array(
            'type'=>$type,
            'source'=>Model_Const::APP_SOURCE_EXCHANGE,
        );
        return $this->setTAuth()->get('/proxy/admin/content/version.json', $data, $returnType);
    }

    public function objectShow($object_id,$returnType = 'json') {
        $data = array(
            'object_id'=>$object_id,
        );
        return $this->setTAuth()->get('/object/show.json', $data, $returnType);
    }

    public function objectModify($object_id,$object,$returnType = 'json') {
        $data = array(
            'object_id'=>$object_id,
            'object'=>$object,
        );
        return $this->setTAuth()->post('/object/modify.json', $data, $returnType);
    }

    public function objectAdd($object,$returnType = 'json') {
        $data = array(
            'object'=>$object,
        );
        return $this->setTAuth()->post('/object/add.json', $data, $returnType);

    }

    public function objectBind($sign,$url,$object,$returnType = 'json') {
        $data = array(
            'sign' => $sign,
            'url' => $url,
            'object'=>$object,
        );
        return $this->setTAuth()->post('/object/secure/import_object.json', $data, $returnType);

    }

}