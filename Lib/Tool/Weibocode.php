<?php
class Tool_Weibocode
{
	public static function create_payment($param, $platform_key)
	{		
		if (isset($param['sign'])) 
		{
			unset($param['sign']);
		}
		
		$payment = new Comm_Weibo_Payment();
		$res = $payment->create_pay($param, $platform_key);
		$res = json_decode($res, true);
		if ($res['error_code'] != '100000')//调用接口返回失败
		{
			Tool_Log::fatal('error_code='.$res['error_code'].'&error='.$res['error']);
			Tool_Redirect::page_not_found();
		}
		return $res ;
	}
}