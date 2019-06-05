<?php
//pay接口调用
class Tool_Pay{

	public static function get_user_account($param){
		try {
			$api = new Comm_Weibo_Pay();
			$rst = $api->get_user_account($param);
			$rst = json_decode($rst, true);
			if ($rst['code'] == 100000){
				return $rst['data'];
			}else{
				return 0;
			}
		} catch (Exception $e) {
			Tool_Log::warning($e->getMessage()) ;
			return ;
		}

	}
}
