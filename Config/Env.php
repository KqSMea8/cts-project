<?php
// JS版本号
//$js_version = Tool_Version::get_js_version($js_version);
$js_version = 1;
$is_https = Comm_Context::is_https();

define('JS_VERSION', "?version=$js_version");

return array(
    //"platform_api_source" 		=>	"1941657700",
    "platform_api_source" 		=>	"1151245325",
    "platform_api_source1" 		=>	"908033280",
    "platform_api_source2" 		=>	"3567117439",//微博转账key
    "platform_api_source3"      =>  "2753274707",//国庆活动key
    "platform_api_source4"      =>  "2727982699",//双十一key,防寒温暖key:1807280623
    "platform_api_source_5"     => 1151245325,//一元夺宝appkey
    "platform_api_source_bonus" =>  "743219212",//红包key
    "platform_api_source_biz"   =>  "3305413945",//商业开放平台key
    "platform_api_source_duobao"   =>  "5954852281",//一元夺宝
    //"platform_api_appsecret" 	=>	"00ede82594b98370bf4681cb133eee7f",
    "platform_api_appsecret" 	=>	"bf6b4d0fdc52b5d8d427e7680ba186c0",
    'applogs_dir' 				=>	Comm_Context::get_server('SINASRV_APPLOGS_DIR'),
    'cache_dir' 				=>	Comm_Context::get_server('SINASRV_CACHE_DIR'),
    'data_dir' 					=>	Comm_Context::get_server('SINASRV_DATA_DIR'),
    'privdata_dir' 				=>	Comm_Context::get_server('SINASRV_PRIVDATA_DIR'),
    'version_js'				=>	$js_version,
    'version_css'				=>	$js_version,
    'version_img'				=>	$js_version,

//		"css_domain"				=>	'http://img.t.sinajs.cn/t4/',
//		"js_domain" 				=>	'http://js.t.sinajs.cn/t4/',
//		"skin_domain"				=>	'http://img.t.sinajs.cn/t4/',
//		'img_domain'				=>	'http://img.t.sinajs.cn/t4/',
//		"css_domain_pool"			=>	array('http://img.t.sinajs.cn/t4/', 'http://img1.t.sinajs.cn/t4/', 'http://img2.t.sinajs.cn/t4/'),

    "css_domain"				=>	$is_https ? 'https://static.weibo.com' : 'http://img.t.sinajs.cn',
    "js_domain" 				=>	$is_https ? 'https://static.weibo.com' : 'http://js.t.sinajs.cn',
    "skin_domain"				=>	$is_https ? 'https://static.weibo.com' : 'http://img.t.sinajs.cn',
    'img_domain'				=>	$is_https ? 'https://static.weibo.com' : 'http://img.t.sinajs.cn',
    "css_domain_pool"			=>	array('http://img.t.sinajs.cn/t4/', 'http://img1.t.sinajs.cn/t4/', 'http://img2.t.sinajs.cn/t4/'),

    'debug' 					=>	'45b5f4beb440454716a4a5d7d34c6d03',
    'keywords_lib'              =>  '1008',
    'event_account'=>array(
        'user'=>'wborder@sina.com',
        'pass'=>'Jenson13',
        'appkey'=>'3895963958',
        'event_sys_admin_appkey'=>'3895963958',
        'event_admin_appkey'=>'3895963958',
        'uid'=>'3674418517'
    ),
    'weibo_pay' => array(
        'uid' => '2850809427',
        'appkey'=> '3567117439',
    ),
    'mcq' => array(
        //默认连接的mcq
        'default' => Comm_Context::get_server('SINASRV_MEMCACHEQ_APP_6802_SERVERS'),
        'orderqueue' => 'appyf.mq.weibo.com:6808',//'10.13.49.132:11212',

    ),
    'redisq' => array(
        //默认连接的redis队列
        'default' => '10.13.49.223:6379',
        'dev' => '10.13.49.223:6378',
    ),
    'domain' => 'http://1.weibo.com',
    'lucky_treasure' => 'lucky_treasure',

    'charge_cash_back'=> true, // 充值返现开关
    'charge_cash_back_type' => 'cash_back',//enum: cash_back, challenge

    'charge_cash_back_tpl_id'=> "5000000005932", //充值返现时调用红包平台所用的模板ID,线上用固定值5000000005932, 测试环境或仿真环境读环境变量

    'special_client_version_product_limit' => true,//特定微博客户端版本商品限制开关
    'special_category_link_list' => array(10),//需要特殊跳转的商品分类

    'template_color' => '#ff0000',
    'template_id'       => array(
        'order_success'     => 'd7e03c2c40085b708c05c3b14dd9b70df91fc00795354f660c0998beb7b015d6',//兑换成功
        'remind_address'    => '3d3a0cd723bc44ce0bddd0e4089f93d1ce1c828705845547b0b1e016fea813a9',//兑换后7天提醒填写收货地址
        'overdue_address'   => '6efa10ec1f2d3a9a83313fc13c7ddf393c8ed4e2f319c58e4ab32f31be55efcc',//兑换后30天提醒填写收货地址
        'delivery'          => 'fe4c49ae8de14cbe08639e973ce8e7d3f32984a370b5d554c9705a7f6f03c48a',//发货提醒
        'money_success'     => '6dc5d3f2df9642714869db1c322d4b27126faee91e907aa48a972170eb6a1e3e',//兑红包通知
        'bonus_success'     => '14dc4f97281da8df7c01716c8d1546822402a3dc75666372b01cd070b8a1c074',//红包发送成功
        'verify_failed'     => 'be1bca07fd656f67748c4e48ccad5e2d45597dfa98f1a9778f07c614a52e6241',//审核失败
        'duiba_order'       => '1e75b81a023e387e7a412830df979893fe60a21b490863d2e9e3a4c169583ae9',//兑吧订单同步成功
    ),
    'official_uid'   => 6685357186,

    'product_id' => array(
        '115589407553',
    ),

    'uid' => array(
        //1305672987,//haoman
        2481472144,//moci
        1898267781,//geyue
        2002949541,//李浩
        5018714685,//宋嘉琪
        3483433630,//李博
        6296045462,//宋福春
        6249636550,//常鸿燕
        1625417465,//韩程

    ),

);
