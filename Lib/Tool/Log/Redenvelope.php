<?php
class Tool_Log_Redenvelope {
	public static function statistic_log($uid, $uicode, $pre_uicode, $action = '') {
		$statistic = array(
				date('Y-m-d H:i:s'), //打码时间
				$uid,                //用户uid
				$action,             //行为码
				'',                  //目标id
				$uicode,          //本页uicode, 收到的红包列表页提现按钮行为统计
				'', '',
				$pre_uicode,          //pre_uicode
				'', '', '', '', '', '', '',
				4, '',
		) ;
		$msg = implode('`', $statistic) ;
		Tool_Log::re($msg) ;
	}
}
