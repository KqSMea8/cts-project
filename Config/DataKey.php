<?php
return array(
    'template_msg_list'                         => 'exchange_template_msg_list',//模版私信队列, list类型
    'pubsub_template_msg_send'                  => 'exchange_pubsub_template_msg_send',//商品发送私信消息队列, list类型
    'user_info_by_uid'                          => 'exchange_user_info_%s',//缓存用户详细信息
    'guess_topic_category'                      => 'zset_guess_category_%s',//分类 topic, 0是全部 hot是热门 new是最新竞猜
    'cache_guess_topic_item'                    => 'string_topic_item_by_tid_%s',//topic 数据缓存
    'cache_guess_group_item'                    => 'string_group_item_by_tid_%s',//group 数据缓存
    'cache_guess_option_item'                   => 'string_option_item_by_gid_%s',//option 数据缓存
    'user_score'                                => 'exchange_score_%s',//用户积分
    'user_jf'                                   => 'exchange_jf_%s',//新用户积分
    'app_token'                                 => 'exchange_app_token_%s',//tauth
    'score_add_fail'                            => 'exchange_score_add_fail_%s',//加积分失败次数
    'score_minus_fail'                          => 'exchange_score_minus_fail_%s',//减积分失败次数
    'group_total_score'                         => 'exchange_group_total_%s_%s',//用户已购买积分
    'set_topic_create_order'                    => 'str_topic_create_order_uid_%s',//topic创建订单uid集合
    'create_order_odds'                         => 'new_exchange_create_order_odds',//动态赔率
    'fail_guess_order_msg'                      => 'new_fail_guess_order_msg',//开奖失败的topic集合
    'lottery_new_notify_msg'                    => 'string_lottery_new_notify_msg',//中奖提醒
    'js_version'                                => 'exchange_js_version_%s',//js版本
    'cache_guess_tags'                          => 'cache_guess_tags_%s',//cache guess_tag
    'today_count'                               => 'today_count_%s_%s', // 用户每天兑换红包金额
    'today_cash_pooling'                        => 'today_cash_polling_%s', //每天可兑换红包奖池金额
    'push_msg_list'                             => 'exchange_push_msg_list_%s',//推送消息参数, list类型
    'user_msg_list'                             => 'exchange_user_msg_list_%s',//推送消息列表, list类型
    'recommend_list'                            => 'recommend_%s'
);
