<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2018/9/30
 * Time: 上午11:57
 */
class Model_Const{
    const ALIAS_EXCHANGE = 'exchange';

    const REDIS_ALIAS_EXCHANGE = 'exchange';
    const REDIS_ALIAS_EXCHANGE_SECURITY_PUSH = 'exchange_security_push';
    const REDIS_ALIAS_EXCHANGE_SECURITY_POP = 'exchange_security_pop';
    const TTL_ONE_MINUTE    = 60;
    const TTL_ONE_HOUR      = 3600;
    const TTL_ONE_DAY       = 86400;
    const TTL_ONE_WEEK      = 604800;

    const APP_SOURCE_EXCHANGE= '1438130149';   //appkey
    const APP_TOKEN_ETC_NAME = 'app_key';//tauth2 etc存储的name
    const APP_TOKEN_ETC_KEY1 = 'tauth2';//tauth2 etc存储的key1

    const DUIBA_URL = 'activity.m.duiba.com.cn';


    //商品类型
    CONST PRODUCT_TYPE_ENTITY = 1;//实体
    CONST PRODUCT_TYPE_VIRTUAL = 2;//虚拟

    //商品状态
    CONST PRODUCT_STATUS_HIDDEN = 0;//已下架(不展示)
    CONST PRODUCT_STATUS_SHOW = 1;//已上架
    CONST PRODUCT_STATUS_WAIT = 2;//待上架

    //订单状态
    const ORDER_STATUS_CLOSE = 0;
    const ORDER_STATUS_CREATE = 1;
    const ORDER_STATUS_PAY = 2;
    const ORDER_STATUS_REFUND = 3;

    //订单发货状态DELIVERY_
    const ORDER_DELIVERY_STATUS_WAIT = 0;//待填写收货地址
    const ORDER_DELIVERY_STATUS_ADDRESS = 1;//已填写收货地址
    //const ORDER_DELIVERY_STATUS_INPUT = 2;//待填写收货地址
    const ORDER_DELIVERY_STATUS_SEND = 3;//已发货
    const ORDER_DELIVERY_STATUS_CONFIRM = 4;//确认收货
    const ORDER_DELIVERY_STATUS_REFUND = 5;//已发货
    const ORDER_DELIVERY_STATUS_CLOSE = 6;//失效，不能添加收货地址
    const ORDER_DELIVERY_STATUS_CODE_WAIT = 11;//待提取卡密
    const ORDER_DELIVERY_STATUS_CODE_GET = 12;//已提取卡密

    //订单推送状态
    const ORDER_PUSH_STATUS_WAIT = 0;//待推送
    const ORDER_PUSH_STATUS_SUCCESS = 1;//推送成功


    //开心乐园账号uid
    const FIRMUid = 6685357186;

    //订单id的长度
    const ORDER_LOGIC_ID_LENGTH = 28;

    //超时未填写收货地址时间
    const DELIVERY_TIMEOUT_DAY = 30;


    const YEWUHAO = 231668; //普通card业务号
    const YEWUHAO_BIG = 231670; //特型大card业务号

    const MONEY_ORDER_STATUS_CLOSE      = 0;//支付失败关闭
    const MONEY_ORDER_STATUS_CREATE     = 1;//待支付
    const MONEY_ORDER_STATUS_PAY        = 2;//支付成功
    const MONEY_ORDER_STATUS_VALIDATE   = 3;//审核中，长时间无响应重新申请
    const MONEY_ORDER_STATUS_REJECT     = 4;//审核拒绝
    const MONEY_ORDER_STATUS_ACCEPT     = 5;//审核成功
    const MONEY_ORDER_STATUS_BONUS      = 6;//发红包成功
    const MONEY_ORDER_STATUS_FAIL       = 7;//发红包失败，等待重试
    const MONEY_ORDER_STATUS_VERIFY     = 8;//核销



    const MONEY_DISPLAY_STATUS_DOING    = 1;//处理中
    const MONEY_DISPLAY_STATUS_FINISH   = 2;//已到账
    const MONEY_DISPLAY_STATUS_REJECT   = 3;//审核失败


    const MSG_TYPE_EXCHANGE = 1;//推送消息-兑换
    const MSG_TYPE_PACKET   = 2;//推送消息-兑钱


}