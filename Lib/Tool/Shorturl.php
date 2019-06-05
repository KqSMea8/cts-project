<?php

class Tool_Shorturl {

	public static function shorten($url) {
		try {
			$api = Comm_Weibo_Api_Shorturl::shorten(array($url));
			$res = $api->get_rst();
			if (isset($res[0]['url_short'])) {
				return $res[0]['url_short'];
			}
		} catch (Exception $e) {
		}
		return $url;
	}

	public static function shorten_info($url) {
		try {
			$api = Comm_Weibo_Api_Shorturl::shorten_info(array($url));
			$res = $api->get_rst();
			if (isset($res['urls'])) {
				return $res['urls'] ;
			}
		} catch (Exception $e) {
		}
		return $url;
	}
	
	/*
	 * @TODO 将长链接转为短链接
	*/
	public static function long_to_short($long_url){
		if(!self::check_long_url($long_url)) {
			throw new Tool_Exception("error_long_url");
		}
		try {
			$api = Comm_Weibo_Api_Shorturl::shorten(array($long_url));
			$short_url = $api->get_rst();
		} catch (Comm_Weibo_Exception_Api $e) {
			throw new Tool_Exception($e);
		}
		return $short_url;
	}
	
	/*
	 * 判断长链接格式是否合法
	*/
	public static function check_long_url($url){
		$url_info = parse_url($url);
		$scheme = isset($url_info['scheme']) ? $url_info['scheme'] : '';
		if (in_array(strtolower($scheme), array('http', 'https'))) {
			return TRUE;
		}
		return FALSE;
	}
}