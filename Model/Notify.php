<?php
class Model_Notify {
    const TEMPLATE_ORDER_SUCCESS = 1;
    const TEMPLATE_REMIND_ADDRESS = 2;
    const TEMPLATE_OVERDUE_ADDRESS = 3;
    const TEMPLATE_DELIVERY = 4;

    const TEMPLATE_MONEY_SUCCESS = 5;
    const TEMPLATE_BONUS_SUCCESS = 6;
    const TEMPLATE_VERIFY_FAILED = 7;

    const TEMPLATE_DUIBA_ORDER = 8;

    protected static function pushTemplateMsgList($receiverId, $templateId, $data, $note, $url, $linkDisplayName)
    {
        $key = Lib_Config::get('DataKey.template_msg_list');
        $map = array(
            'to_uid' => $receiverId,
            'tid' => $templateId,
            'data' => $data,
            'note' => $note,
            'url' => $url,
            'link_name' => $linkDisplayName,
        );

        $redis_obj = new Lib_Redis(Model_Const::REDIS_ALIAS_EXCHANGE, 1);
        return $redis_obj->lpush($key, json_encode($map));
    }

    public static function sendCardMsg($receiverId, $templateId, $data, $note, $url, $linkDisplayName) {
        //顶部颜色
        $topColor = Lib_Config::get('Env.template_color');
        //官方uid
        $officialUid = Lib_Config::get('Env.official_uid');

        $apiWeibo = new Api_Weibo();
        $res = $apiWeibo->epsTemplateSend($officialUid, $receiverId, $templateId, $data, $topColor, $note, $url, $linkDisplayName);
        if (empty($res['result'])) {
            Lib_Log::warning('Model_Notify | send card msg failed | receiver_uid:' . $receiverId . '| sender : '.
                $officialUid .'|time: ' . date('Y-m-d H:i:s', time()) . '| msg:' . json_encode($res));
            return false;
        }

        return true;
    }

    protected static function send($receiverId, $type, $params, $use_list = true){
        switch ($type){
            case self::TEMPLATE_ORDER_SUCCESS:
                $templateId = Lib_Config::get('Env.template_id.order_success');
                $data = array(
                    'time'  => array(
                        'value' => $params['time']
                    ),
                    'name'  => array(
                        'value' => $params['product_name'],
                        'color' => "#666666"
                    ),
                    'price'  => array(
                        'value' => $params['score'],
                    ),
                    'words'  => array(
                        'value' => '积分兑大奖，精彩永不停。',
                    ),
                );
                $url = 'http://t.cn/EyobUbv';
                $note = '';
                $linkDisplayName = '点击查看详情';
                break;
            case self::TEMPLATE_REMIND_ADDRESS:
                $templateId = Lib_Config::get('Env.template_id.remind_address');
                $data = array(
                    'time'  => array(
                        'value' => $params['time']
                    ),
                    'words'  => array(
                        'value' => '积分兑大奖，精彩永不停。',
                    ),
                );
                $url = "http://t.cn/EGdTAbD";
                $note = '';
                $linkDisplayName = '点击查看详情';
                break;
            case self::TEMPLATE_OVERDUE_ADDRESS:
                $templateId = Lib_Config::get('Env.template_id.overdue_address');
                $data = array(
                    'time'  => array(
                        'value' => $params['time']
                    ),
                    'words'  => array(
                        'value' => '积分兑大奖，精彩永不停。',
                    ),
                );
                $url = "http://t.cn/EGdTAbD";
                $note = '';
                $linkDisplayName = '点击查看详情';
                break;
            case self::TEMPLATE_DELIVERY:
                $templateId = Lib_Config::get('Env.template_id.delivery');
                $data = array(
                    'time'  => array(
                        'value' => $params['time']
                    ),
                    'words'  => array(
                        'value' => '积分兑大奖，精彩永不停。',
                    ),
                );
                $url = "http://t.cn/EGdTAbD";
                $note = '';
                $linkDisplayName = '点击查看详情';
                break;
            case self::TEMPLATE_MONEY_SUCCESS:
                $templateId = Lib_Config::get('Env.template_id.money_success');
                $data = array(
                    'time'  => array(
                        'value' => $params['time']
                    ),
                    'name'  => array(
                        'value' => $params['name'],
                    ),
                    'price'  => array(
                        'value' => $params['price'],
                    ),
                );
                $url = 'http://t.cn/EqT78DY';
                $note = '';
                $linkDisplayName = '点击查看详情';
                break;
            case self::TEMPLATE_BONUS_SUCCESS:
                $templateId = Lib_Config::get('Env.template_id.bonus_success');
                $data = array(
                    'time'  => array(
                        'value' => $params['time']
                    ),
                    'price'  => array(
                        'value' => $params['price'],
                    ),
                );
                $url = 'http://t.cn/EqT78DY';
                $note = '';
                $linkDisplayName = '点击查看详情';
                break;
            case self::TEMPLATE_VERIFY_FAILED:
                $templateId = Lib_Config::get('Env.template_id.verify_failed');
                $data = array(
                    'time'  => array(
                        'value' => $params['time']
                    ),
                );
                $url = 'http://t.cn/EqT78DY';
                $note = '';
                $linkDisplayName = '点击查看详情';
                break;
            case self::TEMPLATE_DUIBA_ORDER:
                $templateId = Lib_Config::get('Env.template_id.duiba_order');
                $data = array(
                    'name'  => array(
                        'value' => $params['name']
                    ),
                    'price' => array(
                        'value' => $params['score']
                    ),
                    'words'  => array(
                        'value' => '积分兑大奖，精彩永不停。',
                    ),

                );
                $url = 'http://t.cn/EyobUbv';
                $note = '';
                $linkDisplayName = '点击查看详情';
                break;
            default:
                throw  new Exception('unknown template type');

        }

        if ($use_list) {
            return self::pushTemplateMsgList($receiverId, $templateId, $data, $note, $url, $linkDisplayName);
        }

        return self::sendCardMsg($receiverId, $templateId, $data, $note, $url, $linkDisplayName);
    }

    //兑换成功
    public static function orderSuccrss($map = array(), $use_list = true){
        $params = array(
            'time'          =>$map['time'],
            'product_name'  =>$map['product_name'],//商品名称
            'score'         =>$map['score'],//消耗积分
        );
        return self::send($map['uid'], Model_Notify::TEMPLATE_ORDER_SUCCESS, $params, $use_list);
    }

    //7天未填写收货地址
    public static function remindAddress($map = array(), $use_list = true){
        $params = array(
            'time'   =>$map['time'],
        );
        return self::send($map['uid'], Model_Notify::TEMPLATE_REMIND_ADDRESS, $params, $use_list);
    }

    //29天未填写收货地址
    public static function overdueAddress($map = array(), $use_list = true){
        $params = array(
            'time'   =>$map['time'],
        );
        return self::send($map['uid'], Model_Notify::TEMPLATE_OVERDUE_ADDRESS, $params, $use_list);
    }

    //发货
    public static function delivery($map = array(), $use_list = true){
        $params = array(
            'time'   =>$map['time'],
        );
        return self::send($map['uid'], Model_Notify::TEMPLATE_DELIVERY, $params, $use_list);
    }

    //兑红包
    public static function moneySuccess($map = array(), $use_list = true) {
        $params = array(
            'time'   =>$map['time'],
            'name'   =>$map['name'],//商品名称
            'price'  =>$map['price'],//钱
        );
        return self::send($map['uid'], Model_Notify::TEMPLATE_MONEY_SUCCESS, $params, $use_list);
    }

    //红包发送成功
    public static function bonusSuccess($map = array(), $use_list = true) {
        $params = array(
            'time'   =>$map['time'],
            'price'  =>$map['price'],//钱
        );
        return self::send($map['uid'], Model_Notify::TEMPLATE_BONUS_SUCCESS, $params, $use_list);
    }

    //审核失败
    public static function verifyFailed($map = array(), $use_list = true) {
        $params = array(
            'time'   =>$map['time'],
        );
        return self::send($map['uid'], Model_Notify::TEMPLATE_VERIFY_FAILED, $params, $use_list);
    }

    //兑吧订单同步后发私信
    public static function duibaOrder($map = array(), $use_list = true) {
        $params = array(
            'time'          =>$map['time'],
            'name'          =>$map['name'],//商品名称
            'score'         =>$map['score'],//消耗积分
        );
        return self::send($map['uid'], Model_Notify::TEMPLATE_DUIBA_ORDER, $params, $use_list);
    }

}
