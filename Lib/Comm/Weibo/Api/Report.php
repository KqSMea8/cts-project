<?php
/**
 * 举报接口SDK
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Report {
	const RESOURCE = "report";
	/**
	 * 举报某条信息
	 */
	public static function spam(){
		$url = Comm_Weibo_Api_Request_Platform::assemble_url(self::RESOURCE, "spam");
		$platform = new Comm_Weibo_Api_Request_Platform($url,"POST");
		$platform->add_rule("url", "string",TRUE);
		$platform->add_rule("content", "string",TRUE);
		$platform->add_rule("ip", "string",TRUE);
		$platform->add_rule("type", "int",FALSE);
		$platform->add_rule("rid", "int64",FALSE);
		$platform->add_rule("class_id", "int", FALSE);
		return $platform;
	}
	
	/**
	 * url和status_id的callback 方法
	 */
	public function check_type_rid($platform){
		if($platform->type !== 4 && is_null($platform->rid)){
			throw new Comm_Exception_Program("url and status_id can not be empty at the same time");
		}
	}
}