<?php
class Tool_Status {
	static $substatus = array (
			Do_Order_info::SUBSTATUS_WAITINGREFUND => '<p>退款申请中</p>',
			Do_Order_info::SUBSTATUS_SELLERWITHDRAW => '<p>退款被撤回</p>',
			Do_Order_info::SUBSTATUS_BUYERWITHDRAW => '<p>退款被撤回</p>',
			Do_Order_info::SUBSTATUS_REFUSE_REUND => '<p>退款被拒绝</p>',			
			Do_Order_info::SUBSTATUS_AGREE_REFUND => '<p><span class="txt_style2">退款成功</span></p>',			
			Do_Order_Info::STATUS_REFUND_FAIL => '<p>退款失败</p>',
	);

	public static function get_order_status($params) {
		$is_need_status = Comm_Context::get('is_need_status',false);

		if(!isset($params['status']) || !isset($params['sub_status']))
			return '';
	
		//不是支付、发货
		if(($params['status'] == Do_Order_Info::STATUS_REFUND || 
		   $params['status'] == Do_Order_Info::STATUS_REFUNDED ||
	       $params['status'] == Do_Order_Info::STATUS_REFUNDING ||
	       $params['status'] == Do_Order_Info::STATUS_REFUND_FAIL)&& !$is_need_status) 
		return '';

		//外网和礼物订单
		if($params['otype'] == 1 || $params['otype'] == 2)
		return '';
	
		$sub_status = $params['sub_status'] ; 
		$text = self::$substatus[$sub_status];

		//退款失败
		if ($params['status'] == Do_Order_Info::STATUS_REFUND_FAIL)
		$text = self::$substatus[$params['status']];
		
		if($sub_status == Do_Order_info::SUBSTATUS_AGREE_REFUND && $params['status'] != Do_Order_Info::STATUS_REFUNDED && $params['status'] != Do_Order_Info::STATUS_REFUND_FAIL)
		    $text = '<p><span class="txt_style2">退款中</span></p>';
		
		return $text;
	}    

/*
 * 
 * 获取退款操作
 * 
 * */
	public static function get_refund_action($params) {
		//是否是卖家查看
		$seller = Comm_Context::get('seller',false);
        $viewer = Comm_Context::get('viewer',false);
        
        if( isset($viewer['verified']) && isset($viewer['verified_type']) ) {
            if ((boolean)$viewer['verified'] === true) {
                if ($viewer['verified_type'] == 0) {
                    $icon_type = 'yellow';
                } elseif ($viewer['verified_type'] > 0) {
                    $icon_type = 'blue';
                }
            } 
        }
        
        
        
		if(!isset($params['status']) || !isset($params['sub_status']))
			return '';

		//虚拟订单不支持rma
		if($params['type'] != Do_Order_Info::TYPE_MATERIAL)
			return '';
		
		$url = Comm_Config::get('domain.ordersc');

		if(in_array($params['sub_status'], array(Do_Order_Info::SUBSTATUS_UNKNOWN, Do_Order_Info::SUBSTATUS_ITEM_RESTRICT))){
			if(	$params['disbursement'] > 0 && ($params['status'] == Do_Order_Info::STATUS_PAID ||
				$params['status'] == Do_Order_Info::STATUS_SHIPPED) && !$seller)
				$text = "<a target='_blank' href=\"{$url}/refund/apply?oid={$params['oid']}\">退款</a>";
		}elseif($params['status'] == Do_Order_Info::STATUS_PAID || 
				$params['status'] == Do_Order_Info::STATUS_SHIPPED ||
				$params['status'] == Do_Order_Info::STATUS_REFUND ||
				//$params['status'] == Do_Order_Info::STATUS_REFUNDING ||
				$params['status'] == Do_Order_Info::STATUS_REFUNDED ||
                $params['status'] == Do_Order_Info::STATUS_REFUND_FAIL){
				
				$sub_status = $params['sub_status'] ;
				
				if(isset(self::$substatus[$sub_status])){
					if($seller){
						$url .= "/seller/refund/detail?oid={$params['oid']}";
						//$url = 'http://e.weibo.com/epspcpage/location?frame_url='.urlencode($url);
                        //$url = Comm_Config::get('domain.seller_frame') . '?menu=refund&frame_url=' . urlencode($url);
					}
					else
						$url .= "/refund/detail?oid={$params['oid']}";
                    if($icon_type == "yellow")
                    {
                        $url = Comm_Config::get('domain.ordersc') . "/seller/refund/detail?oid={$params['oid']}";
                        $text = "<a target='rightiframe' href=\"{$url}\">退款详情</a>";
                    } 
                    else
                    {
                        //$text = "<a target='_blank' href=\"{$url}\">退款详情</a>";
                        $text = "<a target='rightiframe' href=\"{$url}\">退款详情</a>";
                    }
			}	
		}
		return $text;
	}
	
	public static function get_status_name($status)
	{
		switch ($status) {
			case Do_Order_Info::STATUS_CLOSED:
				return '已关闭';
			case Do_Order_Info::STATUS_CREATED:
				return '已创建';
			case Do_Order_Info::STATUS_NOTCOMPLETE:
				return '未完善';
			case Do_Order_Info::STATUS_COMPLETED:
				return '已完善';
			case Do_Order_Info::STATUS_UNPAID:
				return '待支付';
			case Do_Order_Info::STATUS_PAYING:
				return '支付中';
			case Do_Order_Info::STATUS_PAID:
				return '支付成功';
			case Do_Order_Info::STATUS_UNSHIPPED:
				return '未发货';
			case Do_Order_Info::STATUS_SHIPPED:
				return '已发货';
			case Do_Order_Info::STATUS_DONE:
				return '已完成';
			case Do_Order_Info::STATUS_CANCELED:
				return '已取消';
			case Do_Order_Info::STATUS_REFUND:
				return '退款中';
			case Do_Order_Info::STATUS_REFUNDED:
				return '已退款';
			case Do_Order_Info::STATUS_REFUND_FAIL:
				return '退款失败';
			case Do_Order_Info::STATUS_PROCESSING:
				return '处理中';
		}
	}
}
