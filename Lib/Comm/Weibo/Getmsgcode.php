<?php
/**
 * 
 * 获取短信验证码操作类
 * 
 * @copyright   weibo.com
 * @author      Stephen <zhangdi3@staff.sina.com.cn>
 * @version     2012-9-4
 */

class Comm_Weibo_Getmsgcode 
{
	//发送短信接口 <--主站提供-->
	const MSG_INTERFACE_URL = "http://qxt.intra.mobile.sina.cn/cgi-bin/qxt/sendSMS.cgi?msg=%s&usernumber=%s&count=%s&from=86680&longnum=106903336612002";

	public function randStr($len=6,$format='NUMBER') 
	{
		switch($format) 
		{
			case 'ALL':
				$chars='abcdefghijklmnopqrstuvwxyz0123456789'; break;
			case 'NUMBER':
				$chars='0123456789'; break;
			default :
				$chars='0123456789';
				break;
		}
		mt_srand((double)microtime()*1000000*getmypid());
		$randCode = "";
		while(strlen($randCode)<$len)
			$randCode.=substr($chars,(mt_rand()%strlen($chars)),1);
		return $randCode;
	}
	
	public function getMsgCode($usernumber)
	{
		if(empty($usernumber))
		{
			throw new Comm_Exception_Program('phonenumber should not be empty');
		}
		$random = self::randStr();
		$url = sprintf(self::MSG_INTERFACE_URL,$random,$usernumber,1);
		$request = new Comm_HttpRequest();
		$request->set_url($url);
		$request->send();
		return $random . $request->get_response_content();
	}
}