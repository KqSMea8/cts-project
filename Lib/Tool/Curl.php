<?php
/**
 * 
 * @version $Id$
 *
 */
class Tool_Curl {

    private static $ch = null;
    private static $error = '';

    /*
       $arrInput = array(
       'url' => '',
       'is_post' => bool,
       'data' => array,
       'connecttimeout' => int,
       'timeout' => int,
       'returntransfer' => bool
       )
     */
    public static function request($arrInput){
		if(!isset($arrInput['url']) || empty($arrInput['url'])){
		    return false;
		}
		$arr = array(
			'is_post' => false,
			'data' => array(),
			'connecttimeout' => 3,
			'timeout' => 3,
			'returntransfer' => true
			);
		foreach($arrInput as $key => $value){
		    $arr[$key] = $value;
		}
		self::$ch = curl_init($arr['url']);
		curl_setopt(self::$ch,CURLOPT_RETURNTRANSFER,$arr['returntransfer']);
		curl_setopt(self::$ch,CURLOPT_POST,$arr['is_post']);
		if(!empty($arr['data'])){
		    if($arr['is_post']){
				curl_setopt(self::$ch,CURLOPT_POSTFIELDS,self::arr2parm($arr['data']));
		    }else{
				curl_setopt(self::$ch,CURLOPT_URL,$arr['url'] . '?' . self::arr2parm($arr['data']));
		    }
		}
		if(isset($arr['cookie']) && $arr['cookie']){
		    /* $cookie = '';
		    foreach($_COOKIE as $k => $v){
				$cookie .= $k . '=' . $v . ';';
		    }
		    $cookie = rtrim($cookie,';'); */
		    /*
		     * Fix the bug 
		     * http://issue.internal.sina.com.cn/browse/WBBIZBUG-9688
		     * 主要是中文只urlencode一次，openapi接口调整还是怎么地，总是返回认证失败
		     */
		    curl_setopt(self::$ch,CURLOPT_COOKIE,Comm_Context::get_server('HTTP_COOKIE'));
		}
	
		curl_setopt(self::$ch,CURLOPT_NOSIGNAL,1);
		curl_setopt(self::$ch,CURLOPT_CONNECTTIMEOUT,$arr['connecttimeout']);
		curl_setopt(self::$ch,CURLOPT_TIMEOUT,$arr['timeout']);
        curl_setopt(self::$ch, CURLOPT_FOLLOWLOCATION, 1);

		$response = curl_exec(self::$ch);
		self::$error = curl_error(self::$ch);
		curl_close(self::$ch);
		
		return $response;
    }

    public static function get_last_error(){
		return self::$error;
    }

    private static function arr2parm($arr){
		if(!is_array($arr) || empty($arr)){
		    return '';
		}
		if(count($arr) == 1){
		    $parm = key($arr) . '=' . (string)current($arr);
		    return $parm;
		}else{
		    $parm = '';
		    foreach($arr as $key => $value){
				//POST方法&和+会丢失
				$value = str_replace('+', '%2B', $value);
				$value = str_replace('&', '%26', $value);
				$parm .= $key . '=' . (string)$value . '&';
		    }
		    $parm = rtrim($parm,'&');
		    return $parm;
		}
    }
}