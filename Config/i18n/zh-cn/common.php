<?php
return array (
    'succeeded' => array(
        'code' => '100000',
        'msg' => '操作成功'
    ),
    'failed' => array(
        'code' => '220001',
        'msg' => '操作失败'
    ),

    'illegal' => array(
        'code' => '220002',
        'msg' => '非法请求'
    ),
    'need_login' => array(
        'code' => '100002',
        'msg' => '请登录'
    ),
    'sign_error' => array(
        'code' => '220004',
        'msg' => '验证签名失败'
    ),
    'incorrect_params' => array(
        'code' => '220005',
        'msg' => '参数错误',
    ),
    'user_not_exist' => array(
        'code' => '220006',
        'msg' => '用户不存在',
    ),
    'info_uncomplete' => array(
        'code' => '220007',
        'msg' => '信息不全',
    ),
    'invalid_user' => array(
        'code' => '220009',
        'msg' => '无效用户'
    ),
    'duplicate_info' => array(
        'code' => '220010',
        'msg' => '重复信息'
    ),
    'wrong_sign_type' => array(
        'code' => '220011',
        'msg' => '签名方式错误'
    ),
    'source_power_error' => array(
        'code' => '220012',
        'msg' => '没有访问权限'
    ),
    'score_not_enough' => array(
        'code' => '220013',
        'msg' => '积分不足',
    ),
    'product_offline' => array(
        'code' => '220014',
        'msg' => '商品已下线',
    ),
    'product_not_enough_stock' => array(
        'code' => '220015',
        'msg' => '商品库存不足',
    ),
    'user_address_empty' => array(
        'code' => '220016',
        'msg' => '用户地址不存在'
    ),
    'product_not_exist' => array(
        'code' => '220017',
        'msg' => '商品不存在',
    ),
    'product_score_change' => array(
        'code' => '220018',
        'msg' => '商品积分变化',
    ),
    'order_uid_error' => array(
        'code' => '320001',
        'msg' => '订单有误',
    ),
    'delivery_timeout' => array(
        'code' => '320002',
        'msg' => '超过30天不能设置收货地址',
    ),
    'pooling_per_day' => array(
        'code' => '220019',
        'msg' => '今日红包已兑完, 明天9:00再来吧',
    ),
    'user_per_day' => array(
        'code' => '220020',
        'msg' => '您今日兑换额度已满, 明天再来吧',
    ),
);
