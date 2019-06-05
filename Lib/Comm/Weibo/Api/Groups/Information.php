<?php
class Comm_Weibo_Api_Groups_Information {
	const RESOURCE = "groups";
	/**
	 * 获取群信息 
	 * @param int $group_id
	 */
	public static function info($group_id){
	    Comm_Weibo_Api_Util::check_int($group_id);
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, $group_id,"json",NULL,FALSE);
		$request = new Comm_Weibo_Api_Request_Platform($url,"GET");
		$request->support_base_app();
		return $request;
	}
	
}