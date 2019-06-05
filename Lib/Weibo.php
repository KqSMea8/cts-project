<?php

class Lib_Weibo
{
    //发送点对点私信
    public static function sendMessage($senderUid, $uid, $content)
    {
        if(empty($content)){
            return false;
        }
        $sender = Lib_Config::get('message.' . $senderUid);
        if(!isset($sender)){
            return false;
        }
        $param = array();
        $param['identity'] = $sender['identity'];
        $param['send_uid'] = $senderUid;
        $param['recv_uid'] = $uid;
        $param['text'] = urlencode($content);
        $param['ts'] = time();

        $_prepare_skey = $param['identity'] . $param['ts'];
        $param['s'] = hash_hmac('sha1', $_prepare_skey, $sender['key']);

        $curl = new Lib_Curl();
        $response = $curl->post('http://i.e.weibo.com/v1/api/message/directnew', array(), $param);
        if ($response['result']) {
            return true;
        }

        Lib_Log::info("fail to send message:" . json_encode($response));
        return false;
   }

}