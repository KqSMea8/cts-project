<?php
class Tool_Eventspe {

	private static $notify_url = 'http://e.weibo.com/v1/api/message/send';
	private static $event_url = 'http://e.weibo.com/v1/api/event/send';
	private static $data_url = 'http://mall.sc.weibo.com/interface/messages/info';
	private static $send_url = 'http://e.weibo.com/v1/api/message/directnew';
    private static $group_send_url = 'http://i.api.weibo.com/groupchat/send_message.json?source=1941657700';
	
	private static $weibopay_identity = '172893601';
	private static $weibopay_s_key = 'dfb9fdfde01bcae8275b71748d84a82d';
	private static $identity = '118687189';
	private static $notify_key = 'notifyevent';
	private static $s_key = 'aa7e720d1e6cccb3a2b0144351eef1a5';

	const PHP_EOL = "\n";
	const SEND_NOTIFY = 1;
	const SEND_EVENT = 2;
	const SEND_BOTH = 3;
	private static $arr_send=array(self::SEND_NOTIFY,self::SEND_EVENT,self::SEND_BOTH);

	const EVENT_PAY_SUC = 'EVENT_00001';
	const EVENT_SHIP = 'EVENT_00002';
	const EVENT_REFUND = 'EVENT_00003';
	const EVENT_REFUND_SUC = 'EVENT_00004';

	private static $arr_event=array(
		self::NOTIFY_PAY_SUC=>self::EVENT_PAY_SUC,
		self::NOTIFY_SHIP_VIRTUAL=>self::EVENT_SHIP,
		self::NOTIFY_SHIP_MATERIAL=>self::EVENT_SHIP,
		self::NOTIFY_REFUND_Y=>self::EVENT_REFUND,
		self::NOTIFY_REFUND_N=>self::EVENT_REFUND,
		self::NOTIFY_REFUND_SUC=>self::EVENT_REFUND_SUC
	);

	const NOTIFY_PAY_SUC = 0;
	const NOTIFY_SHIP_MATERIAL = 1;
	const NOTIFY_SHIP_VIRTUAL= 2;
	const NOTIFY_REFUND_Y = 3;
	const NOTIFY_REFUND_N = 4;
	const NOTIFY_REFUND_SUC = 5;
	
	private static $arr_notify=array(self::NOTIFY_PAY_SUC,self::NOTIFY_SHIP_VIRTUAL,self::NOTIFY_SHIP_MATERIAL,self::NOTIFY_REFUND_Y,self::NOTIFY_REFUND_N,self::NOTIFY_REFUND_SUC);

	//图文私信入对象库
	const NOTIFY_PIC_TEXT_ARTICLE = 'article';
	const NOTIFY_PIC_TEXT_IDENTITY = '118687189';
	const NOTIFY_SEND_IDENTITY  = '150730229' ;
	const NOTIFY_PIC_TEXT_URL = "http://e.weibo.com/v1/api/object/create";

	public static function send_notify($recv_uid, $data)
	{
		
		$param = array();
		$param['identity'] = 150730229;
		$param['send_uid'] = '5126161537';
		$param['recv_uid'] = $recv_uid;
		//$text_param        = 'http://t.cn/8sE2P4Q';
        $text_param = $data ? $data : '#微博踢球#现金大回馈，大批红包赠送中！所有#微博踢球#玩家可领取1次红包，最高金额2014元能否被您抢走呢？测试下自己的人品吧！红包领取有效期为1周，不要错过呦！http://apps.weibo.com/5126161537/QoGcFHf';
		
		$param['text']     = urlencode($text_param);
		
		$param['ts']   = time();
		$_prepare_skey = $param['identity'] . $param['ts'];
		$param['s']    = hash_hmac('sha1', $_prepare_skey, '57f1d9e5940fd7018d0025e3067a98ff');

		$result_temp = self::send(self::$send_url,$param);
		
		Tool_Log::warning (" lefeng_send param :" .var_export($param,true). " result :".$result_temp);
		if(!$result_temp)
		{
			Tool_Log::warning (" lefeng_send error param :".var_export($param,true));
			return false;
		}
		$result = json_decode($result_temp,true);
		if(!$result || !is_array($result))
		{
			Tool_Log::warning (" lefeng_send error param :".var_export($param,true)." result :".$result_temp);
			return false;
		}
		if($result['result'] == 1)
		{
			return true;
		}
		Tool_Log::warning (" lefeng_send error param :".var_export($result,true));
		return false;
	}

	public static function transfer_send_notify($uid, $txt)
	{
		
		$param = array();
		$param['identity'] = 172893601;
		$param['send_uid'] = 2850809427;
		$param['recv_uid'] = $uid;
		
		$param['text']     = urlencode($txt);
		
		$param['ts']   = time();
		$_prepare_skey = $param['identity'] . $param['ts'];
		$param['s']    = hash_hmac('sha1', $_prepare_skey, 'dfb9fdfde01bcae8275b71748d84a82d');

		$result_temp = self::send(self::$send_url,$param);
		
		if(!$result_temp)
		{
			Tool_Log::warning ("transfer message error param :".var_export($param,true));
			return false;
		}
		$result = json_decode($result_temp,true);
		if(!$result || !is_array($result))
		{
			Tool_Log::warning ("transfer message error param :".var_export($param,true)." result :".$result_temp);
			return false;
		}
		if($result['result'] == 1)
		{
			return true;
		}
		Tool_Log::warning ("transfer message error param :".var_export($result,true));
		return false;
	}

    //粉服不限制发送方uid
	public static function send_notify_by_user($sender_uid, $uid, $txt)
	{
		$param = array();
        $senderInfo = Comm_Config::get('notify.notify_user.default');
		$param['identity'] = $senderInfo['identity'];
		$param['send_uid'] = $sender_uid;
		$param['recv_uid'] = $uid;
		
		$param['text']     = urlencode($txt);
		
		$param['ts']   = time();
		$_prepare_skey = $param['identity'] . $param['ts'];
		$param['s']    = hash_hmac('sha1', $_prepare_skey, $senderInfo['key']);

		$result_temp = self::send(self::$send_url,$param);
		
		if(!$result_temp)
		{
			Tool_Log::warning ("notify message error param :".var_export($param,true));
			return false;
		}
		$result = json_decode($result_temp,true);
		if(!$result || !is_array($result))
		{
			Tool_Log::warning ("notify message error param :".var_export($param,true)." result :".$result_temp);
			return false;
		}
		if($result['result'] == 1)
		{
			return true;
		}
		Tool_Log::warning ("notify message error param :".var_export($result,true));
		return false;
	}

	private static function send($url,$param,$is_post = true,$connecttimeout=3,$timeout=3)
	{
		$data = array(
			'url' => $url,
			'is_post' => $is_post,
			'data' => $param,
			'connecttimeout' => $connecttimeout,
			'timeout' => $timeout,
		);
		return Tool_Curl::request($data);
	}

	private static function check_uid($uid)
	{
		if($uid)
		{
			try 
			{
				Comm_Argchecker::string($uid, 'width_min,5;width_max,12;re,/^[0-9]*$/u',
					Comm_Argchecker::NEED, Comm_Argchecker::RIGHT);
			} 
			catch (Comm_Exception_Program $e) 
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		return true;
	}
	
	/**
	 * 获得图文私信的短连接
	 */
	public static function getPicTextMessage()
	{
	    $imgurl_1 = 'http://ww4.sinaimg.cn/mw1024/c43d4309tw1efu63l9xhkj20b40b4t9e.jpg'; //638
	    $imgurl_2 = 'http://ww4.sinaimg.cn/mw1024/c43d4309tw1efu63m4p9mj20b40b4jrr.jpg'; //645
	    
		$msg = array(
			'identity'  => self::NOTIFY_PIC_TEXT_IDENTITY,
			'owner_uid' => '1832259747',
		);
		
		$data_tmp= array();
		   
        $msg_tmp1 = array(
                'display_name' => '欢型.Lucky 56植绒印花宽松款短T，限量100件，10:00开售',
                'summary'      => '欢型.Lucky 56植绒印花宽松款短T，限量100件，10:00开售',
                'cover'        => $imgurl_1,
                'content'      => '欢型.Lucky 56植绒印花宽松款短T，限量100件，10:00开售',// TODO 需修改
                'url'          => 'http://weibo.com/p/100122_1832259747_638',
                'object_type'  => self::NOTIFY_PIC_TEXT_ARTICLE
        );
        
        $msg_tmp2 = array(
                'display_name' => '欢型.Lucky 56植绒印花显瘦款短T，限量100件，10:00开售',
                'summary'      => '欢型.Lucky 56植绒印花显瘦款短T，限量100件，10:00开售',
                'cover'        => $imgurl_2,
                'content'      => '欢型.Lucky 56植绒印花显瘦款短T，限量100件，10:00开售',// TODO 需修改
                'url'          => 'http://weibo.com/p/100122_1832259747_645',
                'object_type'  => self::NOTIFY_PIC_TEXT_ARTICLE
        );
		
        $data_tmp[] = $msg_tmp1;
        $data_tmp[] = $msg_tmp2;
		
		$data_send = array(
			array(
				'display_name' => '抢购即将开始，微博独家限量发售，更有机会赢取谢娜签名照，抓紧机会吧！',
				'summary'      => '抢购即将开始，微博独家限量发售，更有机会赢取谢娜签名照，抓紧机会吧！',
				'cover'        => 'http://ww2.sinaimg.cn/mw1024/c43d4309tw1efu1yn7kcpj20fk08o3zq.jpg',
				'content'      => '抢购即将开始，微博独家限量发售，更有机会赢取谢娜签名照，抓紧机会吧！',
				'url' => 'http://weibo.com/p/1006061832259747',
				'object_type' => 'article'
			)
		); 
        
		$data_send = array_merge($data_send, $data_tmp);
		echo '<pre>';print_r($data_send);exit;
		$msg['data'] = json_encode($data_send);
		$msg['data'] = urlencode( $msg['data']);
		$msg['ts'] = time();
		$_prepare_skey = $msg['identity'] . $msg['ts'];
		$msg['s'] = hash_hmac('sha1', $_prepare_skey, self::$s_key);
		$result = Model_AbstractE::curl(self::NOTIFY_PIC_TEXT_URL, $msg, 4000, 'post', 'json');

		return $result;
	}
	
	/*public static function sendRedenvelopMsg($owner_uid, $set_id, $amount, $quantity)
	{
		$page_id = $set_id . '_' . $owner_uid ;
		$object = array(
					"id" => '2022782001:create_' . $page_id,
					"display_name" => "红包已包好通知",
					"image" => array(
							"url" => "http://img.t.sinajs.cn/t4/appstyle/red_envelope/images/mobile/private_letter_complete2.png",
							"width" => 560,
							"height" => 300
					),
					"custom_data" => array(
							"remark" => "",
					) ,
					"summary" => sprintf('%.2f元已装入%d个红包。 7天未被领取的红包金额将被原路退回。', $amount, $quantity),
					"url" =>  Comm_Config::get('domain.ordersc') . '/redenvelope/recvdetailowner?set_id=' . $page_id,
					"links" => array(
							"url" => Comm_Config::get('domain.ordersc') . '/h5/redenvelope/recvdetailowner?set_id=' . $page_id . '&sinainternalbrowser=topnav&portrait_only=1',
					),
					"create_at" => date('Y-m-d H:i:s'),
					"object_type" => "webpage",
		) ;
		
		$url = Comm_Config::get('domain.ordersc') . '/redenvelope/recvdetailowner?set_id=' . $page_id . '_create';
		$rst = Dw_Object::bind_object(json_encode($object), $url, 2) ;
		if ($rst['result'] == true) {//5136362277  使用“微博红包”的账号发送
			$sendrecv = Tool_Eventspe::send_notify_for_redenvelope('5136362277', $owner_uid, $rst['short_url']) ;
			if (!$sendrecv) {
				Tool_Log::warning('Redenvelope send notify to create failed.' . var_export($rst, true));
			}
			return true ;
		} 
		return false ;
	}*/
	
	public static function sendRedenvelopMsg($owner_uid, $set_id, $amount, $quantity) {
		
		$template_id = '44bf248b3570d43a723e8fac837f2cac6b59247103abeccd53a4e1c34134a97e' ;
		
		$data = array(
			'date_time'    => array(
				'color' => '#929292',
				'value' => date('n月j日 H:i'),
			),
			'amount'=> array(
				'color' => '#E14123',
				'value'=>sprintf("%.2f", round($amount, 2)),
			),
			'total_packet' => array(
				'color' => '#E14123',
				'value'=>$quantity,
			), 
			'comment'=> array(
				'color' => '#929292',
				'value'=>'7天未被领取完的金额将原路退回',
			),
		) ;
		
		$sendrecv = Tool_Redenvelope_Notify::send_template_msg($template_id, $owner_uid, $set_id, $data) ;
		
		if (!$sendrecv || !$sendrecv['result']) {
			Tool_Log::warning('Redenvelope send notify to create failed.' . Tool_Log_Tostring::arr2str($sendrecv));
			//return false; //为防止发私信接口超时中断主流程，这里只记录失败原因，不返回false
		}
		
		return true ;
	}
	
	public static function send_notify_duobao($recv_uid, $content)
	{
		if (empty($recv_uid) || empty($content)) {
			return false;
		}
		$param = array();
		$param['identity'] = '174075443';
		$param['send_uid'] = '5954852281';
		$param['recv_uid'] = $recv_uid;
		
		$text_param = $content;
	
		$param['text']     = urlencode($text_param);
	
		$param['ts']   = time();
		$_prepare_skey = $param['identity'] . $param['ts'];
		$param['s']    = hash_hmac('sha1', $_prepare_skey, '7c403a2adae444a9d041ba0101ee6d01');
	
		$result_temp = self::send(self::$send_url,$param);
	
		Tool_Log::warning (" redenvelope_send param :" .json_encode($param). " result :".$result_temp);
		if(!$result_temp)
		{
			Tool_Log::warning ("send msg error param :".json_encode($param));
			return false;
		}
		$result = json_decode($result_temp,true);
		if(!$result || !is_array($result))
		{
			Tool_Log::warning ("send msg error param :".json_encode($param)." result :".$result_temp);
			return false;
		}
		if($result['result'] == 1)
		{
			return true;
		}
		Tool_Log::warning (" redenvelope_send error param :".json_encode($result,true));
		return false;
	}

    public static function  send_group_msg($data){
        $api = Comm_Weibo_Api_Messages::send_group_msg();
        $api->id          = $data['id'];
        $api->content     = $data['content'];
        $api->fids        = $data['fids'];
        $api->latitude    = $data['latitude'];
        $api->longitude   = $data['longitude'];
        $api->mblogid     = $data['mblogid'];
        $api->ip          = $data['ip'];
        $api->annotations = $data['annotations'];
        $res = $api->get_rst();

        if ($res['result'] == true){
            return true;
        }

        Tool_Log::warning ("group msg send error:" . var_export($res,true));
        return false;
    }
}
