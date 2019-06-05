<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2019/3/1
 * Time: 3:17 PM
 */
class Model_PushMsg{

    public static function pushExchangeMsg($uid, $product){
        return self::push(Model_Const::MSG_TYPE_EXCHANGE, $uid, $product['score'], $product['name']);
    }
    public static function pushPacketMsg($uid, $packet){
        return self::push(Model_Const::MSG_TYPE_PACKET, $uid, $packet['score'], $packet['money']);
    }

    protected static function push($type, $uid, $score, $name) {

        switch ($type){
            case Model_Const::MSG_TYPE_EXCHANGE:
            case Model_Const::MSG_TYPE_PACKET:
                $data = array(
                    'type'  => $type,
                    'uid'   => $uid,
                    'score' => $score,
                    'name'  => $name,
                );
                break;
            default:
                return false;
        }

        return Model_Cache::pushExchangeMsg(json_encode($data));
    }

    public static function pop(){
        $msg = Model_Cache::popExchangeMsg();
        if(empty($msg)){
            return false;
        }
        $params = json_decode($msg, true);
        switch ($params['type']) {
            case Model_Const::MSG_TYPE_EXCHANGE:
                $userInfo = Model_User::getUserLiteInfo($params['uid']);
                $cost     = $params['score'] . '积分';
                $get      = $params['name'];
                break;
            case Model_Const::MSG_TYPE_PACKET:
                $userInfo = Model_User::getUserLiteInfo($params['uid']);
                $cost     = $params['score'] . '积分';
                $get      = $params['name'] . '元红包';
                break;
            default:
                return '';
        }
        if(empty($userInfo['screen_name'])){
            return '';//防止用户信息获取失败
        }
        return "@{$userInfo['screen_name']} 刚刚用{$cost} 兑换了{$get}";
    }
}