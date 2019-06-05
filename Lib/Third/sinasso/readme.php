<?php
//	SSOConfig.php中新增USE_WEIBO_ALC配置，暂时只有主站需要配置
//	SSOWeiboCookie.php和SSOWeiboClient.php需要部署到动态平台的php包含路径中	/usr/local/sinasrv2/lib/php/




/*
 weibo验证用户登录状态的大致流程：
	1. SUE/SUP验证
		SSOWeiboCookie->getCookie()验证SUE/SUE，成功后通过getsso获取用户信息
	2. ALF/WEIBOALC验证
		通过chkmini接口验证alc的有效性，如果无效，继续向下运行
	3. 调用SSOClient->isLogined()
		如果存在GET[ticket]，验证；如果存在COOKIE[SSOLoginState]或COOKIE[ALF]，跳转到会员中心验证

这层逻辑都包含在了SSOWeiboClient的isLogined()方法中
 */


function test() {
	//根据域名判断加载文件
	$host = $_SERVER['SERVER_NAME'];
	if (stripos($host, 'weibo.com') !== false) {
		include_once PATH . '/SSOConfig.php';	//for *.weibo.com
		include_once 'SSOWeiboCookie.php';
		include_once 'SSOWeiboClient.php';
	} else {
		include_once PATH . '/SSOConfig.php';	//for *.sina.com.cn
		include_once 'SSOCookie.php';
		include_once 'SSOClient.php';
	}
	$sso = new SSOClient();
	if ($sso->isLogined()) {
		//已登录
	}
	//未登录
}

/**
 * 合并后微博主站的判断逻辑
 * 
 * 原来的注释说明：
 *	现在启用的是优化后的代码，上线时把本段代码注释，开启下方注释的代码，本段代码做为第二次上线内容
 *	这句话指定的位置在SSOWeiboCookie的autoLogin方法中
 * 
 * 问题说明：
 *	原来微博主站在识别WEIBOALC时，为防止一次http请求产生多次alc验证请求，加了一层缓存
 *	此次开发时，直接将第一次获取的cookie信息赋值给$_COOKIE，来避免相同问题
 *	实际上将SSOWeiboClient写为单件模式会更好，但会改动原来的使用方式
 * 
 * @return mixed
 */
function isLogined() {
	include_once SERVER_ROOT.'/tools/sso/SSOConfig.php';
	include_once 'SSOWeiboClient.php';
	$sso = new SSOClient();
	if ($sso->isLogined()) {
		return $sso->getUserInfo();
	}
	return false;
}
