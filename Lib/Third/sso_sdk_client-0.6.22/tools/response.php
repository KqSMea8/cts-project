<?php

/**
 * Class Sso_Sdk_Tools_Response
 */
class Sso_Sdk_Tools_Response{
	/**
	 * 重定向
	 * @param $url string
	 */
	public static function redirect($url){
		@header('Cache-Control: no-cache, no-store');
		@header('Location: '.$url);
	}

	/**
	 * 设置http响应头
	 * @param $name string
	 * @param $value mixed
	 */
	public static function header($name, $value){
		@header("$name:$value", false);
	}
}