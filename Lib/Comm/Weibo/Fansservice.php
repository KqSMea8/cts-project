<?php
class Comm_Weibo_Fansservice {
	const TAMPLATE_NOSTICE = 'https://api.weibo.com/2/eps/template/send.json' ;

	
	/**
	 *
	 * access_token true 	string 	在粉丝服务平台 - 高级功能 - 开发者模式页面中获取，或者OAuth2.0授权后获得, 详细参考 获取粉丝服务平台开发接口的access token。
	 * receiver_id 	true 	long 	消息接收方的ID。
	 * template_id 	true 	string 	用户模板的ID，utid。
	 * data 		true 	string 	要发给用户的数据，json格式，还需补充该json格式的说明文档
	 * url 			false 	string 	如果是发送纯文本格式的消息，则无需该字段
	 * topcolor 	false 	string 	消息顶部颜色，如果是发送纯文本格式的消息，则无需该字段
	 *
	 *
	 */
	public static function send_template_msg() {
		try {
			$platform = new Comm_Weibo_Api_Request_ThirdModule(self::TAMPLATE_NOSTICE, 'POST') ;
			$platform->set_check_succ_ret(false) ;
				
			$platform->add_rule('access_token', 'string', true) ;
			$platform->add_rule('receiver_id', 'string', true) ;
			$platform->add_rule('template_id', 'string', true) ;
			$platform->add_rule('data', 'string', true) ;
			$platform->add_rule('url', 'string', false) ;
			$platform->add_rule('topcolor', 'string', false) ;
				
			return $platform ;
		} catch (Exception $e){
			Tool_Log::warning($e->getMessage()) ;
			return ;
		}
	}
}