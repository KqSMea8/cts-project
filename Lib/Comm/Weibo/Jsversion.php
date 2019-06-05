<?php
/**
 * 应用广场接口操作类
 *
 * @package    Comm
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     jianbin <jianbin@staff.sina.com.cn>
 * @version    2012-1-11
 */

class Comm_Weibo_JsVersion {
    //获取JS/CSS版本号
    const GET_JS_VERSION_URL = "http://i.api.weibo.com/2/proxy/admin/content/version.json?source=1941657700&type=26"; 
   
    /**
     * 获取GET请求的响应内容
     *
     * @param string $url
     * @param BOOL $is_raw_url 是否直接使用原始url，此参数解决GET参数传递数组的情况，如id[]=xxx&id[]=yyy
     * @return mixed
     */
    public static function get_response_result($url, $is_raw_url = FALSE) {
    	$request = new Comm_HttpRequest ();
    	if ($is_raw_url === FALSE) {
    		$request->set_url ( $url );
    	} else {
    		$request->url = $url;
    	}
    	$request->send ();
    	return $request->get_response_content ();
    }
    
    
	/**
	 * 获取JS/CSS版本号
	 * @param unknown_type $appkey
	 * @param unknown_type $page
	 * @param unknown_type $num
	 * @throws Comm_Exception_Program
	 */
	public static function get_js_version(){		
		$url = self::GET_JS_VERSION_URL;
		$response = self::get_response_result($url, TRUE);
		$result = json_decode($response, true);
		if(empty($result) || !isset($result['result'])){
			return mt_rand(1, 100000) . time();
		}
		return $result['result'];
	}
   	
}
