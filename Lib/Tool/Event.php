<?php
class Tool_Event {

	private static $notify_url = 'http://e.weibo.com/v1/api/message/send';
	private static $event_url = 'http://e.weibo.com/v1/api/event/send';
	private static $data_url = 'http://mall.sc.weibo.com/interface/messages/info';
	private static $identity = '172893601';
	private static $notify_key = 'notifyevent';
	private static $s_key = 'dfb9fdfde01bcae8275b71748d84a82d';

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
	const NOTIFY_PIC_TEXT_IDENTITY = '172893601';
	const NOTIFY_PIC_TEXT_URL = "http://e.weibo.com/v1/api/object/create";

	private static function send_notify($recv_uid,$send_uid,$data){
		Tool_Log::warning (" the function send_notify recv_uid :{$recv_uid} send_uid:{$send_uid} data :{$data}");
		$param =array();
		$param['identity']=self::$identity;
		$param['recv_uid']=$recv_uid;
		$param['send_uid']=$send_uid;
		$param['type']='text';
		$arr['text'] = $data;
		$arr_json = json_encode($arr);
		$param['data'] = urlencode($arr_json);
		//加入签名校验 hash_hmac('sha1', $identity . $ts, $pin)
		$param['ts'] = time();
		$_prepare_skey = $param['identity'] . $param['ts'];
		$param['s'] = hash_hmac('sha1', $_prepare_skey, self::$s_key);

		$result_temp = self::send(self::$notify_url,$param);
		Tool_Log::warning (" send_notify param :" .var_export($param,true). " result :".$result_temp);
		if(!$result_temp){
			Tool_Log::warning (" send_notify error param :".var_export($param,true));
			return false;
		}
		$result = json_decode($result_temp,true);
		if(!$result || !is_array($result)){
			Tool_Log::warning (" send_notify error param :".var_export($param,true)." result :".$result_temp);
			return false;
		}
		if($result['result'] == 1){
			return true;
		}
		Tool_Log::warning (" send_notify error param :".var_export($result,true));
		return false;
	}

	private static function send_event($event_id,$recv_uid,$send_uid){
		Tool_Log::warning (" the function send_event event_id :{$event_id} recv_uid:{$recv_uid} send_uid :{$send_uid}");
		$param =array();
		$param['identity']=self::$identity;
		$param['recv_uid']=$recv_uid;
		$param['send_uid']=$send_uid;
		$param['event_id']=$event_id;
		//加入签名校验 hash_hmac('sha1', $identity . $ts, $pin)
		$param['ts'] = time();
		$_prepare_skey = $param['identity'] . $param['ts'];
		$param['s'] = hash_hmac('sha1', $_prepare_skey, self::$s_key);

		$result_temp = self::send(self::$event_url,$param);
		Tool_Log::warning (" send_event result :".$result_temp);
		if(!$result_temp){
			Tool_Log::warning (" send_event error param :".var_export($param,true));
			return false;
		}
		$result = json_decode($result_temp,true);
		if(!$result || !is_array($result)){
			Tool_Log::warning (" send_event error param :".var_export($param,true)." result :".$result_temp);
			return false;
		}
		if($result['result'] == 1){
			return true;
		}
		Tool_Log::warning (" send_event error param :".var_export($result,true));
		return false;
	}

	private static function send($url,$param,$is_post = true,$connecttimeout=3,$timeout=3){
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
		if($uid){
			try {
				Comm_Argchecker::string($uid, 'width_min,5;width_max,12;re,/^[0-9]*$/u',
					Comm_Argchecker::NEED, Comm_Argchecker::RIGHT);
			} catch (Comm_Exception_Program $e) {
				return false;
			}
		}else{
			return false;
		}
		return true;
	}

	public static function send_event_notify_New($send_uid,$recv_uid,$data,$type,$send_type=self::SEND_BOTH){
		Tool_Log::warning (" the function send_event_notify param send_uid : {$send_uid}  recv_uid : {$recv_uid}  type : {$type}  send_type : {$send_type} data :".var_export($data,true));
		$event = true;
		if($send_type == self::SEND_BOTH || $send_type == self::SEND_EVENT ){
			//$event = self::send_event(self::$arr_event[$type],$recv_uid,$send_uid);
			$event = self::send_event(self::$arr_event[$type],$send_uid,$recv_uid);//接收者发事件用来给发送者增加配额 20130331粉服张漉
		}
		if(!$event){//发送事件失败
			return false;
		}
		$notify = true;
		if($send_type == self::SEND_BOTH || $send_type == self::SEND_NOTIFY ){
			if(isset($data)){
				$message_self = self::get_notify_by_firmId($send_uid, $type, $data);
				if(!$message_self){
					if(is_bool($message_self)){
						return true;
					}else{
						$message_self = '';
					}
				}
				$message = self::getMessage($type,$data,$send_uid,$message_self);
				if(!$message && is_bool($message)){
					return false;
				}
				$notify = self::send_notify($recv_uid,$send_uid,$message);
			}
		}
		return $notify;
	}

	private static function get_notify_by_firmId($send_uid, $type, $data){
		Tool_Log::warning (" the function get_notify_by_firmId send_uid : {$send_uid} type :{$type}");
		$param =array();
		$param['firm_id']=$send_uid;
		$result_temp = false;
		$size = 0;
		while(!$result_temp && $size < 3){
			$result_temp = self::send(self::$data_url,$param,false);
			$size++;
		}
		Tool_Log::warning (" notify message send_uid : {$send_uid} type :{$type} result :".$result_temp);
		if(!$result_temp){
			return false;
		}
		$result = json_decode(urldecode($result_temp),true);
		if(!$result || !is_array($result)){
			return false;
		}
		$status = $result['status'];
		if($status != 1){
			return false;
		}
		$message = $result['messages'];
		if(!$message){
			return false;
		}
		//$message = json_decode(urldecode($message),true);
		if(isset($message[$type])){
			if($type == self::NOTIFY_PAY_SUC && is_array($message[$type]) && count($message[$type])){
				//图文私信
				$result = self::getPicTextMessage($send_uid, $type, $data, $message);
			}else{
				//文本私信
				$result = $message[$type];
			}
		}else{
			$result = false;
		}
		return $result;
	}


	public static function sendNotifyToMcqNew($send_uid,$recv_uid,$type,$data_temp=array(),$send_type=self::SEND_BOTH){
		Tool_Log::warning ( "The function sendNotifyToMcq param send_uid : {$send_uid} recv_uid : {$recv_uid}  type : {$type} send_type : {$send_type} data_temp:".var_export($data_temp,true));
		$data['send_uid'] = $send_uid;
		$data['recv_uid'] = $recv_uid;
		$data['type'] = $type;
		$data['send_type'] = $send_type;
		$data['data'] = $data_temp;
		$res = false;
		$size = 0;
		Tool_Log::warning (" Write mcq key : ".self::$notify_key."  data :".var_export($data,true));
		while(!$res & $size < 3){
			try{
				$res = Mcq::mcqWrite(self::$notify_key , $data);
			}catch (Exception $e){
				Tool_Log::warning (" Write mcq key : ".self::$notify_key."  data :".var_export($data,true)." error :".$e->getMessage());
				$size ++;
				continue;
			}
			if(!$res){
				$size ++;
			}
		}
		Tool_Log::warning (" Write mcq key : ".self::$notify_key."  data :".var_export($data,true)." res :".$res);
	}

	private static function getMessage($type,$data,$send_uid,$message_self){
		Tool_Log::warning ( "The function getMessage param type : {$type}   send_uid :{$send_uid}  message_self  :{$message_self} data : ".var_export($data,true));
		if(!$data || !$send_uid){
			Tool_Log::warning ( "The getMessage  data or send_uid is null");
			return false;
		}
		$oid = $data['oid'];
		/*$model_order_info = new Model_Order_Info();
		$do_order_info = $model_order_info->get_order_info_from_master($oid);
		if(!$do_order_info){
			Tool_Log::warning ( "The order_info id : {$oid} is null");
			return false;
		}
		*/
		//图文私信message处理
		if($type == self::NOTIFY_PAY_SUC && is_array($message_self) && $message_self['result'] == 1 ){
			$message = $message_self['data']['short_url'];
			Tool_Log::warning (" compose notify pic text message send_uid : {$send_uid} type :{$type} message :".$message);
			return $message;
		}

		$model_order_detail = new Model_Order_Detail();
		$commodity_infos = $model_order_detail->orderCommodityInfos($send_uid, $oid);
		if(!$commodity_infos || !$commodity_infos[0]){
			Tool_Log::warning ( "The commodity_infos  oid : {$oid}   firm_id: ".$send_uid."  is null");
			return false;
		}//*/

		$common = "订单编号：".$oid.self::PHP_EOL;
		$common .= "商品名称：".$commodity_infos[0]['commodity_info']['name'];
		$count = count($commodity_infos);
		if($count > 1){
			$common .= "等{$count}件商品";
		}

		if($type == self::NOTIFY_PAY_SUC){
			$title = "尊敬的用户，您的订单已经支付成功";
			$message_temp = "付款金额：{$data['mount']}元".self::PHP_EOL.
				"付款时间：{$data['time']}".self::PHP_EOL.
				"交易单号：{$data['trade_no']}";
		}else if($type == self::NOTIFY_SHIP_MATERIAL){
			$title = "尊敬的用户，您的订单已发货，请保持联系电话的畅通";
			$message_temp = "发货时间：{$data['update_time']}".self::PHP_EOL.
				"物流公司：{$data['logistic']}".self::PHP_EOL.
				"物流单号：{$data['waybill']}";
		}else if($type == self::NOTIFY_SHIP_VIRTUAL){
			$title = "尊敬的用户，您的订单已发货";
			$message_temp = "发货时间：{$data['update_time']}".self::PHP_EOL.
				"优惠券/码：{$data['waybill']}";
		}else if($type == self::NOTIFY_REFUND_Y){
			$title = "尊敬的用户，您发起的退款申请已被受理，退款资金将在1-3个工作日内返回您的付款账户";
			$message_temp = "订单金额：{$data['mount']}".self::PHP_EOL.
				"退货编号：{$data['rid']}";

		}else if($type == self::NOTIFY_REFUND_N){
			$title = "尊敬的用户，您发起的退款申请已被驳回";
			if(!isset($data['phone'])){
				$model_firm_info = new Model_Firm_Info();
				$do_firm_info = $model_firm_info->get_firm_info($send_uid);
				$data['phone'] = $do_firm_info['hotline'];
			}
			$message_temp = "订单金额：{$data['mount']}".self::PHP_EOL.
				"退货编号：{$data['rid']}".self::PHP_EOL.
				"驳回原因：{$data['refuse_info']}".self::PHP_EOL.
				"客服电话：{$data['phone']}";
		}else if($type == self::NOTIFY_REFUND_SUC){
			$title = "尊敬的用户，您的退款已完成，请留意账户的资金变动";

			$message_temp = "退款单号：{$data['rid']}".self::PHP_EOL.
				"退款金额：{$data['mount']}".self::PHP_EOL.
				"交易单号：{$data['trade_no']}";
		}
		$detail_url = Comm_Config::get('domain.ordersc') . '/order/detail?orderid=' .$oid;
		$message = $title.self::PHP_EOL.self::PHP_EOL.$common.self::PHP_EOL.$message_temp.self::PHP_EOL.'查看详情：'.$detail_url;
		if(is_string($message_self)){
			$message .=  self::PHP_EOL.self::PHP_EOL.$message_self;
		}

		return $message;
	}

	/*    private static function getTradeMessage($data,$type){
			if(!isset($data['trade_id'])){
			   return $data;
			}
			$oid = $data['oid'];
			if($type == Tool_Event::NOTIFY_PAY_SUC){
				$model_payment = new Model_Alipay_Payment();
				$res = $model_payment->get_payment_by_oid_tradestatus($oid,'TRADE_SUCCESS', Tool_AliPay_Service::VERIFY_TYPE_NOTIFY);
				if($res){
					$data['mount'] = $res['total_fee'];
					$data['trade_no'] = $res['trade_no'];
					$data['time'] = $res['create_time'];
				}
			}else if($type == Tool_Event::NOTIFY_REFUND_Y || $type == Tool_Event::NOTIFY_REFUND_N){
				$refund_apps = Dr_Order_RefundApplication::get_refund_info($oid,$data['trade_id']);
				if($refund_apps){
					$refund = $refund_apps[0];
					if($refund){
						$data['mount'] = $refund['amount'];
						$data['rid'] = $refund['rid'];
						if($type == Tool_Event::NOTIFY_REFUND_N){
							$data['refuse_info'] = $refund['refuse_info'];
							$model_firm_info = new Model_Firm_Info();
							$do_firm_info = $model_firm_info->get_firm_info($data['firm_id']);
							if($do_firm_info){
								$data['phone'] = $do_firm_info['hotline'];
							}
						}
					}
				}
			}else if($type == Tool_Event::NOTIFY_REFUND_SUC){
				$model_refund = new Model_Alipay_Refund();
				$res = $model_refund->get_refund_op_info_by_batchno($data['trade_id']);
				if($res){
					$data['mount'] = $res['amount'];
					$data['trade_no'] = $res['batch_no'];
					$data['mount'] = $res['amount'];
				}
			}
		}*/

	/*
	 * 拼装支付成功时需要用到的数据
	 * oid :订单id
	 * mount:退款金额
	 * trade_no:交易号
	 * time:支付成功的时间
	 */
	public static function getPayMessage($oid,$mount,$trade_no,$time){
		$param = array();
		$param['oid'] = $oid;
		$param['mount'] = $mount;
		$param['trade_no'] = $trade_no;
		$param['time'] = $time;
		Tool_Log::warning ( "The function getPayMessage param :".json_encode($param));
		return $param;
	}
	/*
	* 拼装私信需要用到的数据
	* oid :订单id
	* trade_id:(支付成功 notify_payment_info的id,退款成功,失败就是refund_application的rid,退款成功就是notify_refund_info的id)
	*/
	public static function getTradeMessagePutMcq($oid,$trade_id){
		$param = array();
		$param['oid'] = $oid;
		$param['trade_id'] = $trade_id;
		Tool_Log::warning ( "The function getPayMessageNew param :".json_encode($param));
		return $param;
	}

	/*
	 * 拼装退款(同意退款,拒绝退款,退款成功)的私信需要用到的数据
	 * oid :订单id
	 * rid :退款的rid号(同意退款,拒绝退款填写)
	 * mount:退款金额
	 * trade_no:交易号
	 * refuse_info:拒绝原因(拒绝退款填写)
	 * phone:商家热线(拒绝退款填写)
	 */
	public static function getRefoundMessage($oid,$rid,$mount,$trade_no='',$refuse_info = '',$phone = null){
		$param = array();
		$param['oid'] = $oid;
		$param['mount'] = $mount;
		$param['trade_no'] = $trade_no;
		$param['rid'] = $rid;
		$param['refuse_info'] = $refuse_info;
		if(!is_null($phone)){
			$param['phone'] = $phone;
		}
		Tool_Log::warning ( "The function getRefoundMessage param :".json_encode($param));
		return $param;
	}

	/*
	 * 拼装发货(实物,虚拟)的私信需要用到的数据
	 * oid :订单id
	 */
	public static function getShipMessage($oid,$update_time='',$logistic='',$waybill=''){
		$param = array();
		$param['oid'] = $oid;
		$param['update_time'] = $update_time;
		$param['logistic'] = $logistic;
		$param['waybill'] = $waybill;
		Tool_Log::warning ( "The function getShipMessage param :".json_encode($param));
		return $param;
	}

	/**
	 * 获得图文私信的短连接
	 */
	private static function getPicTextMessage($send_uid, $type, $data, $message){

		$msg = array(
			'identity'  => self::NOTIFY_PIC_TEXT_IDENTITY,
			'owner_uid' => $send_uid,
		);
		$data_tmp= array();
		foreach($message[$type]  as $v){
			if($v){
				$msg_tmp = array(
					'display_name' => $v['title'],
					'summary'      =>  $v['title'],
					'cover'         => $v['image_url'],
					'content'      => $v['title'],// TODO 需修改
					'url'           => $v['url'],
					'object_type' => self::NOTIFY_PIC_TEXT_ARTICLE
				);
				$data_tmp[] = $msg_tmp;
			};
		}

		$model_order_detail = new Model_Order_Detail();
		$commodity_infos = $model_order_detail->orderCommodityInfos($send_uid, $data['oid']);
		if(!$commodity_infos || !$commodity_infos[0]){
			Tool_Log::warning ( "The commodity_infos  oid : {$data['oid']}   firm_id: ".$send_uid."  is null");
			return false;
		}

		$_cover_name = "订单" . $data['oid'] . "支付成功";
		$_detail_url = Comm_Config::get('domain.ordersc') . '/order/detail?orderid=' .$data['oid'];
		$data_send = array(
			array(
				'display_name' => $_cover_name,
				'summary' => $_cover_name,
				'cover' => $commodity_infos[0]['commodity_info']['img_url'],
				'content' => $_cover_name,
				'url' => $_detail_url,
				'object_type' => 'article'
			)
		);

		$data_send = array_merge($data_send, $data_tmp);
		$msg['data'] = json_encode($data_send);
		$msg['data'] = urlencode( $msg['data']);
		//$msg['data'] =urlencode(json_encode($data));
		//加入签名校验 hash_hmac('sha1', $identity . $ts, $pin)
		$msg['ts'] = time();
		$_prepare_skey = $msg['identity'] . $msg['ts'];
		$msg['s'] = hash_hmac('sha1', $_prepare_skey, self::$s_key);
		//$result = array('result'=>1, 'data'=>array("short_url" => "http://t.cn/8scyaW6"));
		$result = Model_AbstractE::curl(self::NOTIFY_PIC_TEXT_URL, $msg, 4000, 'post', 'json');
		//$result = Dw_Objects_Notify::add($msg);  // TODO 通用的入对象库方法 入对象库，获得短链接
		Tool_Log::warning (" notify pic text message send_uid : {$send_uid} type :{$type} result :".$result);

		return $result;
	}

}