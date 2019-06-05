<?php
/**
 * 微博-支付宝项目        调用平台接口，操作打点数据
 * @author      chengmeng@staff.sina.com.cn
 * @copyright   Weibo.com
 * 
 */
class Comm_Weibo_Api_Changecount
{
	const ADDCOUNT = 'http://i.api.weibo.com/2/remind/add_count.json' ;
	const SETCOUNT = 'http://i.api.weibo.com/2/remind/set_count.json' ;
	
	private static $_operate = array('add', 'set') ;
	
	/*
	*  @param       uid
	*  @param       source  AppKey
	*  @param       type    打点类型   order or cards
	*  @param       value   设置的打点值，默认为1
	*  @param       operate 操作类型 add or set
	*  @return      bool
	*/
	public static function operate_count($uid, $source, $type, $value, $operate) //type ： order or cards
	{
		if (!in_array($operate, self::$_operate)) 
		{
			throw new Dr_Exception('Operate Invalid');
		}
		if ($operate === 'add') 
		{
			$url = self::ADDCOUNT ;
		}
		else 
		{
			$url = self::SETCOUNT ;
		}
		
		$request = new Comm_Weibo_Api_Request_Platform($url, 'POST') ;
		$request -> add_rule('uid', 'int64', TRUE) ;
		$request -> add_rule('source', 'string', TRUE) ;
		$request -> add_rule('type', 'string', TRUE) ;
		$request -> add_rule('value', 'int', TRUE) ;
				
		$request->uid = $uid ;
		$request->source = $source ;
		$request->type = $type ;
		$request->value = $value ;
		
		return $request ;
	}

}