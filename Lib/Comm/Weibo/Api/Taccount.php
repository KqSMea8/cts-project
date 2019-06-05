<?php
/**
 * 微博与淘宝和支付宝绑定的api接口
 *
 * @package    
 * @copyright  copyright(2015) weibo.com all rights reserved
 * @author     weibin<weibin@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Taccount {

    const APP_SECRET = '5cae45e2d58257b3da2f67397fd6e5f2';

	public static function get($uid){
		$url = "http://i2.api.weibo.com/2/taccount/v2/get.json";
        $request = new Comm_HttpRequest($url);
        $request->add_query_field('uid',$uid);
        $request->add_query_field('sign',md5(base64_encode("uid={$uid}").self::APP_SECRET));
        $request->send();
        $content = $request->get_response_content();
		return $content;
	}
	
	
	public static function update($uid,$ali_id,$ali_email) {
		$url = "http://i2.api.weibo.com/2/taccount/v2/update_ali.json";
		$request = new Comm_HttpRequest($url);
		$request->add_post_field('uid',$uid);
		$request->add_post_field('ali_id',$ali_id);
		$request->add_post_field('ali_email',$ali_email);
		$request->add_post_field('bind_source',45);
		$request->add_post_field('sign',md5(base64_encode("ali_email={$ali_email}&ali_id={$ali_id}&bind_source=45&uid={$uid}").self::APP_SECRET));
		$request->send();
		$content = $request->get_response_content();
		return $content;
	}

}
