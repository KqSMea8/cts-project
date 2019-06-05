<?php
class Tool_Notify {

    private static $url = 'https://api.weibo.com/2/notification/send.json';
    private static $event_sys_admin_appkey = '';
	private static $event_admin_appkey = '';
    private static $ch;

   
    // 订单
    const TPL_ORDER_CREATE = '3605739740034007'; // 订单生成
    const TPL_ORDER_PAY = '3605740150896281'; // 支付成功
    const TPL_ORDER_SEND = '3605741778338725'; // 发货
    
    // --- 礼物订单
    const TPL_ORDER_GIFT_RECEIVED = '3625765688016695'; // to 送礼者：收礼者接收礼物
    const TPL_ORDER_GIFT_REJECT = '3625765754880847'; // to 送礼者：收礼者拒绝礼物/逾期未处理
	const TPL_ORDER_GIFT_PAY = '3625765591289835'; // to 收礼者：礼物支付成功
	const TPL_ORDER_GIFT_SHIP_SEND = '3625765880720121'; // to 送礼者：礼物订单发货
	const TPL_ORDER_GIFT_SHIP_RECEIVE = '3625765939183660'; // to 收礼者：礼物订单发货
	
	
    // letv
    const TPL_LETV_APPOINTMENT = '3558716580075467'; // 预约成功
    const TPL_LETV_BEFORE_BUYING = '3558717209284867'; // 抢购开始前
    const TPL_LETV_BUY_SUCCEED = '3558717603589793'; // 抢购成功
    const TPL_LETV_BEFORE_CANCEL = '3558725371072783'; // 订单作废前
    
    const LETV_BUY_URL = 'http://e.weibo.com/1833784983/app_2228121531 ';

	static $tpls = array(
		'refund_agree' 	=> '3647851558643740',		//退款中
		'refund_refuse' => '3647852821466750',		//拒绝退款
		'refund_expire' => '3647853337399886',		//即将过期提醒
	    'deliver'       => '3605741778338725',		//实物发货
	    'deliver_virtual' => '3605739740034007',		//虚拟发货	    
	);    
    
//------初始化----------------------
    private static function init(){
		self::$ch = curl_init(self::$url);
		$account = Comm_Config::get('env.event_account');
		$userpwd = $account['user'].':'.$account['pass'];
		self::$event_sys_admin_appkey = $account['event_sys_admin_appkey'];
		self::$event_admin_appkey = $account['event_admin_appkey'];
		curl_setopt(self::$ch,CURLOPT_POST,1);
		curl_setopt(self::$ch,CURLOPT_USERPWD,$userpwd);
		curl_setopt(self::$ch,CURLOPT_RETURNTRANSFER,1);
    }

	/**
	 * 通用的发送通知方法
	 * @param int $tpl_id 模板ID
	 * @param int $uids uid字符串，逗号隔开
	 * @param array $tpl_data	模板数据
	 * @return mixed
	 */
	//写消息队列，2013-08-12修改 
    private static function _notify($tpl_id, $source, $uids, $tpl_data){
	
		$tpl_data['tpl_id']    = $tpl_id;
		$tpl_data['source']    = $source;
		$tpl_data['uids']      = $uids;

		if(!Mcq::mcqWrite('notify', $tpl_data, 'json')) {
			Tool_Log::warning("|MCQ-LOG|NOTIFY|write-mcq-error|-- " . var_export($tpl_data, true));
			return false;
		}
		return true;
	}


	 /**
	  * @param int tpl_id	模板id
	  * @param string uids	uid字符串,逗号隔开
	  * @param string act_title	活动名称
	  * @param string url	通知附带URL
	  * @return mixed
	 */
    private static function _notice($tpl_id, $uids, $act_title, $url){
        self::init();
		$data = array('objects1' => $act_title, 'action_url' => Dr_Shorturl::shorten($url));
		return self::_notify($tpl_id, self::$event_sys_admin_appkey, $uids, $data);
    }
    
//---------------------------------------------------------------------
 
    public static function order_create($uids, $orderid, $url){
        
        self::init();
        $tpl_data = array(
                'objects1' => $orderid,
                'action_url' => Dr_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_ORDER_CREATE, self::$event_admin_appkey, $uids, $tpl_data);
    }
    public static function order_apy($uids, $orderid, $epsid, $phone, $url){
        self::init();
        $tpl_data = array(
                'objects1' => $orderid,
                'objects2' => $epsid,
                'objects3' => $phone,
                'action_url' => Dr_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_ORDER_PAY, self::$event_admin_appkey, $uids, $tpl_data);
    }
    public static function order_send($uids, $orderid, $firm, $phone, $url){
        self::init();
        $tpl_data = array(
                'objects1' => $orderid,
                'objects2' => $firm,
                'objects3' => $phone,
                'action_url' => Dr_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_ORDER_SEND, self::$event_admin_appkey, $uids, $tpl_data);
    }
    
	//---------------------------------------------------------------------	
    // letv notify
    //---------------------------------------------------------------------
    // 预约成功 抢购链接
    public static function letv_appointment($uids, $url){
        self::init();
        $tpl_data = array(
        	'action_url' => Dr_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_LETV_APPOINTMENTE, self::$event_admin_appkey, $uids, $tpl_data);
    }
    // 抢购开始前 抢购链接
    public static function letv_before_buying($uids, $url){
        self::init();
        $tpl_data = array(
                'action_url' => Dr_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_LETV_BEFORE_BUYING, self::$event_admin_appkey, $uids, $tpl_data);
    }
    // 抢购成功 订单详情页链接
    public static function letv_buy_succeed($uids, $text, $before_time, $firm_name, $url){
        self::init();
        $tpl_data = array(
                'objects1' => $text,
                'objects2' => $before_time,
                'objects3' => $firm_name,
                'action_url' => Dr_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_LETV_BUY_SUCCEED, self::$event_admin_appkey, $uids, $tpl_data);
    }
    // 订单作废前 订单详情页链接
    public static function letv_abefore_cancel($uids, $text, $firm, $url){
        self::init();
        $tpl_data = array(
                'objects1' => $text,
                'objects2' => $firm,
                'action_url' => Dr_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_LETV_BEFORE_CANCEL, self::$event_admin_appkey, $uids, $tpl_data);
    }
    
    /**
     * to 送礼者：收礼者接收礼物
     * @param string $uid    	送礼者
     * @param string $receiver  收礼者
     * @param string $url	 	订单详情页url
     */
    public static function order_gift_received($uid, $receiver, $url) {
        self::init();
        $tpl_data = array(
                'objects1'=>$receiver,
                'action_url'=>Tool_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_ORDER_GIFT_RECEIVED, self::$event_admin_appkey, $uid, $tpl_data);
    }
    
    /**
     * to 送礼者：收礼者拒绝礼物
     * @param string $uid    	送礼者
     * @param string $receiver 	收礼者
     * @param string $url	 	订单详情页url
     */
    public static function order_gift_reject($uid, $receiver, $url) {
        self::init();
        $tpl_data = array(
                'objects1'=>$receiver,
                'action_url'=>Tool_Shorturl::shorten($url)
        );
        return self::_notify(self::TPL_ORDER_GIFT_REJECT, self::$event_admin_appkey, $uid, $tpl_data);
    }
    
	/**
	 *
	 * @param string $uid    收礼者
	 * @param string $giver  送礼者
	 * @param string $url	 拆包页url
	 */
	public static function order_paid_receiver($uid, $giver, $url) {
		self::init();
	    $tpl_data = array(
	            'objects1'=>$giver,
	            'action_url'=>Tool_Shorturl::shorten($url)
	    );
	    return self::_notify(self::TPL_ORDER_GIFT_PAY, self::$event_admin_appkey, $uid, $tpl_data);
	}
	/**
	 * to 送礼者：礼物订单发货
	 * @param string $uid		送礼者
	 * @param string $firm_id	商户
	 * @param string $receiver	收礼者
	 * @param string $url		订单详情页url
	 */
	public function order_gift_ship_sender($uid, $firm_id, $receiver, $url) {
		self::init();
		$tpl_data = array(
				'objects1'=>$firm_id,
				'objects2'=>$receiver,
				'action_url'=>Tool_Shorturl::shorten($url)
		);
		return self::_notify(self::TPL_ORDER_GIFT_SHIP_SEND, self::$event_admin_appkey, $uid, $tpl_data);
	}	
	/**
	 * to 收礼者：礼物订单发货
	 * @param string $uid		收礼者
	 * @param string $giver		送礼者
	 * @param string $url		订单详情页url
	 */
	public function order_gift_ship_receiver($uid, $giver, $url) {
		self::init();
		$tpl_data = array(
				'objects1'=>$giver,
				'action_url'=>Tool_Shorturl::shorten($url)
		);
		return self::_notify(self::TPL_ORDER_GIFT_SHIP_RECEIVE, self::$event_admin_appkey, $uid, $tpl_data);
	}
	
	
	public function order_shipped($uid, $order_id, $firm_id, $phone, $url) {
		self::init();
		$tpl_data = array(
				'objects1'=>$order_id,
				'objects2'=>$firm_id,
				'objects3'=>$phone,
				'action_url'=>Tool_Shorturl::shorten($url)
		);
		return self::_notify(self::TPL_ORDER_SEND, self::$event_admin_appkey, $uid, $tpl_data);
	}	

	/**
	 * 发送通知
	 * @param int $action 操作类别
	 * @param int $uids uid字符串，逗号隔开
	 * @param array $tpl_data	模板数据
	 * @return mixed
	 */
	//写消息队列，2013-08-12修改
	public function send_notify($type, $uids, $tpl_data){
		self::init();

		if(!isset(self::$tpls[$type]))
			return false;
		$tpl_id = self::$tpls[$type];

		if($tpl_data['action_url'])
			$tpl_data['action_url'] = Tool_Shorturl::shorten($tpl_data['action_url']);
		
		self::_notify($tpl_id, self::$event_admin_appkey, $uids, $tpl_data);
		return true;
	}	
}