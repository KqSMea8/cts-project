<?php
class Tool_Redenvelope_Notify {
	const SEND_UID = 5954852281;
	const ACCESS_TOKEN = '2.00XGxzUGzUVuPBd2cfab3fbd0W6GhH';//'2.00r9ebbFgrz5HC9841f39d5c0UC5tb' ;
	const REDIS_ALIAS = 'duobao';
	
	public static function pushTemplateMsgList($map)//($template_id, $owner_uid, Array $data, $url)
	{
		$key = Lib_Config::get('DataKey.template_msg_list');
//		$map = array(
//			'tid' => $template_id,
//			'touid' => $owner_uid,
//			'msg' => $data,
//			'url' => $url,
//		);

		$redis_obj = new Lib_Redis(self::REDIS_ALIAS, 1);
		return $redis_obj->lpush($key, json_encode($map));
	}

	/**
	 * @param unknown $template_id
	 * @param array $data
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
	public static function send_template_msg($template_id, $owner_uid, Array $data, $url) {
		try {
			Tool_Log::info('send_template_msg | set_id = ' . $set_id . ' data:' . Tool_Log_Tostring::arr2str($data)) ;
			$api = Comm_Weibo_Fansservice::send_template_msg() ;
			$api->access_token = self::ACCESS_TOKEN ;
			$api->receiver_id = $owner_uid ;
			$api->template_id = $template_id ;
			$api->data = json_encode($data) ;
			$api->url = $url;
			
			$rst = $api->get_rst() ;
			return $rst ;
		} catch (Exception $e) {
			Tool_Log::warning($e->getMessage()) ;
			return false;
		}
		
	}
	
	private static function msg_data($params, $type) { //type 1， 给领取者发送的数据， 2，创建者发送数据
		$page_id = $params['set_id'] . '_' . $params['recv_uid'] ;
		if ($type == 1) {
			if ($params['creater_uid'] == $params['recv_uid']) {
				$desti_uri = 'recvdetailowner';
			} else {
				$desti_uri = 'receivedetail';
			}
			$object = array(
					"id" => '2022782001:withdraw_' . $page_id,
					"display_name" => "成功抢到红包通知",
					"image" => array(
							"url" => "http://img.t.sinajs.cn/t4/appstyle/red_envelope/images/mobile/private_letter_sendout2.png",
							"width" => 560,
							"height" => 300
					),
					"custom_data" => array(
							"remark" => "",
					) ,
					"summary" => sprintf('你已成功抢到@%s 发出的群组红包，抢到的金额为：%.2f元', $params['owner_name'], $params['bonus']),
					"url" =>  Comm_Config::get('domain.ordersc') . '/redenvelope/' . $desti_uri . '?set_id=' . $page_id,
					"links" => array(
							"url" => Comm_Config::get('domain.ordersc') . '/h5/redenvelope/' . $desti_uri . '?set_id=' . $page_id . '&sinainternalbrowser=topnav&portrait_only=1',
					),
					"create_at" => date('Y-m-d H:i:s'),
					"object_type" => "webpage",
			) ;
			$url = Comm_Config::get('domain.ordersc') . '/redenvelope/receivedetail?set_id=' . $page_id . '_withdraw';
		} else {
			$object = array(
					"id" => '2022782001:send_' . $page_id,
					"display_name" => "红包被领取通知",
					"image" => array(
							"url" => "http://img.t.sinajs.cn/t4/appstyle/red_envelope/images/mobile/private_letter_get2.png",
							"width" => 560,
							"height" => 300
					),
					"custom_data" => array(
							"remark" => "",
					) ,
					"summary" => sprintf('你发出的群组红包被@%s 领走了一个，领取金额：%.2f元。 共发%d个红包，总金额%.2f元（已被领走%d个，领走总金额%.2f元）', $params['recv_name'], $params['bonus'], $params['quantity'], $params['amount'], $params['quantity_recv'], $params['amount_recv']),
					"url" =>  Comm_Config::get('domain.ordersc') . '/redenvelope/recvdetailowner?set_id=' . $page_id,
					"links" => array(
							"url" => Comm_Config::get('domain.ordersc') . '/h5/redenvelope/recvdetailowner?set_id=' . $page_id . '&sinainternalbrowser=topnav&portrait_only=1',
					),
					"create_at" => date('Y-m-d H:i:s'),
					"object_type" => "webpage",
			) ;
			$url = Comm_Config::get('domain.ordersc') . '/redenvelope/recvdetailowner?set_id=' . $page_id . '_send';
		}
		$rst = Dw_Object::bind_object(json_encode($object), $url, 2) ;
		if ($rst['result'] != true) {
			$uri = $url;
		} else {
			$uri = $rst['short_url'] ;
		}
		return $uri ;
	}
	
	public static function receive_notify(Array $data) {

		$send_to_recv = self::msg_data($data, 1) ;
			
		Tool_Log::warning(json_encode($send_to_recv));
		$sendrecv = Tool_Eventspe::send_notify_for_redenvelope(self::SEND_UID, $data['recv_uid'], $send_to_recv) ;
		if (!$sendrecv) {
			Tool_Log::warning('Redenvelope send notify to recv failed.' . json_encode($sendrecv));
		}
		
		if ($data['notify_flag'] == 1) {
			$send_to_create = self::msg_data($data, 2) ;
			
			Tool_Log::warning(json_encode($send_to_create));
			$sendberecved = Tool_Eventspe::send_notify_for_redenvelope(self::SEND_UID, $data['creater_uid'], $send_to_create) ;
			if (!$sendrecv) {
				Tool_Log::warning('Redenvelope send notify to create failed.' . json_encode($sendberecved));
			}
		}
		return  true;
	}
}
