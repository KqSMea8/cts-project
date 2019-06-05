<?php
/**
 * 文本关键字检查
 *
 * @author  	chengmeng
 * @version 	2014-04-22
 * @copyright  	copyright(2014) weibo.com all rights reserved
 *
 */
class Comm_Weibo_Api_Detectkeyword
{
	const DETECT_URL = 'http://i.admin.weibo.com/admin/checkkeyword/detect_by_lib.json' ;
	const TAOBAO_URL = 'https://huluwa.secure.alibaba.com/requestLimited.do' ;
	/*
	 *  lib: 	进行检测的关键词库 	GET
	 *	appid: 	调用方APPID 	POST
	 *	content: 	进行关键词检测的文本内容 	POST
	 *	withoutsass: 	0：检测反垃圾关键词（默认）；1：不检测反垃圾关键词 	POST
	 */
	
	public static function detect_keywords(Array $data, $libid)
	{
		try {
			$request = new Comm_HttpRequest() ;
			$request->url = self::DETECT_URL . '?lib=' . $libid;
			foreach ($data as $key => $value)
			{
				$request->add_post_field($key, $value) ;
			}
			
			$ren= $request->send();
			$result = $request->get_response_content();
			return $result ;
		} catch (Exception $e)
		{
			Tool_Log::warning($e->getMessage());
			return false;
		}
	}
	
	public static function detect_by_taobao(Array $data) {
		try {
			$request = new Comm_HttpRequest() ;
			$request->url = self::TAOBAO_URL ;
			$request->set_method('POST') ;
			foreach ($data as $key => $value) {
				if ($value !== '') {
					$request->add_post_field($key, $value) ;
				}
			}
			$ren = $request->send() ;
			$res = $request->get_response_content() ;
			return $res ;
		} catch (Exception $e) {
			Tool_Log::warning($e->getMessage()) ;
			return false ;
		}
	}
}