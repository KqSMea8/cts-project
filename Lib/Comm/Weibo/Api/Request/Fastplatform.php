<?php
/**
 * openapi接口请求类
 *
 * @package  api  
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     cunsheng<cunsheng@staff.sina.com.cn>,dengzheng<dengzheng@staff.sina.com.cn>
 */
class Comm_Weibo_Api_Request_Fastplatform extends Comm_Weibo_Api_Request_Abstract {
    public static $platform_api_server_name = "http://i.api.weibo.com";
    public static $platform_api_version_least = "1";
    public static $platform_api_default_format = "json";
    public static $platform_api_server_name_v3 = "http://api.t.sina.com.cn";
    public static $platform_api_server_name_internal = "http://i.api.t.sina.com.cn";
	public static $platform_api_server_name_v5 = "http://i2.api.weibo.com/2";  

    /**
     * 
     * @param unknown_type $url
     * @param unknown_type $method
     */
    public function __construct($url, $method = false,$key = '', $timeout) {
        parent::__construct($url, $method);
        $this->http_request->connect_timeout = $timeout;
        $this->http_request->timeout = $timeout;
        
         /**
          * 需要修改openapi http认证信息 sue sup 经由thirdpart SDK 处理 rawurldecode SUE/SUP
          * add_cookie 会 rawurlencode SUW cookie，所以需要预先 rawurlencode
          * @see  T3PPATH.'/sinasso/SSOWeiboCookie.php';
          * 
          */
        $use_suw = Comm_Context::get('use_suw', -1);
        $h5suw = Comm_Context::get('h5suw','');
        if(1 === $use_suw) {
            $this->http_request->add_cookie("SUW", isset($_COOKIE["SUW"]) ? rawurldecode($_COOKIE["SUW"]) : '');
        }else {
            $this->http_request->add_cookie("SUE", isset($_COOKIE["SUE"]) ? $_COOKIE["SUE"] : '');
            $this->http_request->add_cookie("SUP", isset($_COOKIE["SUP"]) ? $_COOKIE["SUP"] : '');
        }
        
        if ($h5suw) 
        {
        	if (count($h5suw) == 1) 
	        {
	        	$this->http_request->add_cookie("SUW", $h5suw[0]);
	        }else 
	        {
	        	$this->http_request->add_cookie("SUE", $h5suw[0]);
	        	$this->http_request->add_cookie("SUP", $h5suw[1]);
	        }
  		}
        
        if (!$key){
            if(strtoupper($method) == "POST"){
                $this->http_request->add_post_field("source", Comm_Util::conf("env.platform_api_source"));
            }else if(strtoupper($method) == "GET"){
            	$this->http_request->add_query_field("source", Comm_Util::conf("env.platform_api_source"));
            }
        }else if ($key == 1){//发私信的
            if(strtoupper($method) == "POST"){
                $this->http_request->add_post_field("source", Comm_Util::conf("env.platform_api_source1"));
            }
        }else if ($key == 2){//微博转账的key
            if(strtoupper($method) == "POST"){
                $this->http_request->add_post_field("source", Comm_Util::conf("env.platform_api_source2"));
            }
        }
        else if($key == 3) // 轻应用key
        {
            if(strtoupper($method) == "POST"){
                $this->http_request->add_post_field("source", Comm_Util::conf("env.platform_api_source3"));
            }
        }
        else if($key == 4) // 轻应用key
        {
            if(strtoupper($method) == "POST"){
                $this->http_request->add_post_field("source", Comm_Util::conf("env.platform_api_source4"));
            }
        }
        else if($key == 5) // 红包key
        {
            if(strtoupper($method) == "POST"){
                $this->http_request->add_post_field("source", Comm_Util::conf("env.platform_api_source_bonus"));
            }
        }
        else if($key == 9) // 商业开放平台
        {
            if(strtoupper($method) == "POST"){
                $this->http_request->add_post_field("source", Comm_Util::conf("env.platform_api_source_biz"));
            }
        }
    }

    /**
     * 接口请求方法
     * @see Comm_Weibo_Api_Request_Abstract::get_rst()
     * @return 接口无异常时的正常返回值
     */
     public function get_rst($throw_exp = TRUE,$defaut = array()) {
         //使用OAuth2认证方式   ---shiliang5
         $oauth_uid = Comm_Context::get('oauth_uid', "");
         $oauth_auth_string = Comm_Context::get('oauth_auth_string', "");
         if (!empty($oauth_uid) && !empty($oauth_auth_string) && $this->usepsd == false) {
             $url_encode = $this->http_request->urlencode;
             $this->http_request->urlencode = false;
             $this->http_request->add_header("Authorization", $oauth_auth_string,false);
             $this->http_request->urlencode = $url_encode;
         }
        parent::send();
        $content = $this->http_request->get_response_content();
		$result = Comm_Util::json_decode($content, true);
        $exp_msg = $exp_code = FALSE;
        if ($this->http_request->get_response_info('http_code') != '200') {
        	
            if(isset($result['error'])){
        	    $exp_msg = $result['error'];
        	    $exp_code = $result['error_code'];
        	}
        	else{
        	    $exp_msg = "http error:".$this->http_request->get_response_info('http_code');
        	    $exp_code = $this->http_request->get_response_info('http_code');
        	}
        }elseif(!is_array($result)){
    	        $exp_msg = "api return data can not be json_decode";
    	        $exp_code = -1;
        }
        if(FALSE !== $exp_code && FALSE !== $exp_msg){
            $this->write_error_log();
            if($throw_exp == TRUE){
               throw new Comm_Weibo_Exception_Api($exp_msg,$exp_code);
            }
            else{
                    return $defaut;
            }
        }
        return $result;  
    }
    
    /**
     * 记录openapi异常日志
     * 
     */
    public function write_error_log() {
        if (!isset($_SERVER['SINASRV_APPLOGS_DIR']) || !is_dir($_SERVER['SINASRV_APPLOGS_DIR'])) {
            return;
        }
        $log_dir = $_SERVER['SINASRV_APPLOGS_DIR'].'openapi/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir);
            chmod($log_dir, 0777);
        }
        
        $log_file = $log_dir.''.date('Ymd').'.log';
        
        $response_info = $this->http_request->get_response_info();
        $content = $this->http_request->get_response_content();
        $result = Comm_Util::json_decode($content, true);
        $http_code= $response_info['http_code'];
        $total_time = $response_info['total_time'];
        $url = $response_info['url'];
        $logs = array();
        $exp_msg = '';
        if (200 != $http_code) {
            if (isset($result['error'])) {
                $logs[] = 'ERROR:';
                $exp_msg = join('', $result);
            } else {
                $logs[] = 'SYSERR:';
                $exp_msg = "http error:" . $this->http_request->get_response_info('http_code');
            }
        } else {
            $logs[] = 'INFO:';
            if (!is_array($result)) {
                $exp_msg = "api return data can not be json_decode";
            }
        }
        
        $logs[] = date('Y-m-d H:i:s');
        $logs[] = Comm_Context::get_client_ip();
        $logs[] = '['.$http_code.']';
        $logs[] = $total_time;
        $logs[] = $url;
        $logs[] = $exp_msg;
        $logs[] = "\n";
        error_log(join(' ', $logs), 3, $log_file);
        return;
    }
    
    /**
     * 添加翻页参数的统一方法
     * @param string $page_name
     * @param string $offset_name
     */
    public function support_pagination($page_name = "page", $offset_name = "count") {
        parent::add_rule($page_name, "int", false);
        parent::add_rule($offset_name, "int", false);
    }
    
    /**
     * 添加base_app参数的统一方法
     */
    public function support_base_app() {
        parent::add_rule("base_app", "string", false);
    }
    
    /**
     * 添加trim_user参数的统一方法
     */
    public function support_trim_user() {
        parent::add_rule("trim_user", "int", false);
    }
    
    /**
     * 添加trim_status参数的统一方法
     */
    public function support_trim_status() {
        parent::add_rule("trim_status", "int", false);
    }
    
    /**
     * 添加游标参数的统一方法
     */
    public function support_cursor($since_id_name="since_id", $max_id_name="max_id"){
    	parent::add_rule($since_id_name, "int64");
    	parent::add_rule($max_id_name, "int64");
    }
    
    /**
     * 当uid和screen_name互斥时的添加方法
     * Enter description here ...
     * @param string $uid_name uid参数名
     * @param string $screen screen_name参数名
     * @param boolean $is_batch 是否为批量值
     */
    public function uid_or_screen_name($uid_name = 'uid', $screen = 'screen_name', $is_batch = false){
        parent::add_rule($uid_name, "string");
        parent::add_rule($screen, "string");
    	if($is_batch) {
            parent::add_set_callback($uid_name, 'Comm_Weibo_Api_Util', 'check_batch_values', array('int64', ',', 20));
            parent::add_set_callback($screen, 'Comm_Weibo_Api_Util', 'check_batch_values', array('string', ',', 20));
        }
        parent::add_before_send_callback('Comm_Weibo_Api_Util', "check_alternative", array($uid_name, $screen));
    }
    
    /**
     * 生成接口url
     * @param string $resource
     * @param string $interface
     * @param string $format
     * @param string $version
     * @param bool $is_v4
     * @param $pre_query
     * @throws Comm_Exception_Program
     */
    public static function assemble_url($resource, $interface, $format = "json", $version = NULL, $is_v4 = TRUE, $pre_query = ''){
        if (isset($version) && $version < self::$platform_api_version_least){
            throw new Comm_Exception_Program("api least version: ".self::$platform_api_version_least);
        }
        
        if (empty($format)){
            $format = self::$platform_api_default_format;
        }
        
        if(TRUE === $is_v4){
           $url = self::$platform_api_server_name.'/'.$resource; 
        }
        else{
            	$arr =explode("/",$interface);
            if(in_array($resource, array("iremind","remind","groups","short_url", 'nav'))) {
               $url = self::$platform_api_server_name_internal.'/'.$resource;
            }elseif($arr[0] == 'deliver_address'){
            	    $url = self::$platform_api_server_name_v5.'/'.$resource;
            }else {
               $url = self::$platform_api_server_name_v3.'/'.$resource;
            }
        }
       
        $url .= (isset($version) ? '/'.$version : "");
        if(!empty($interface)) {
            $url .= '/'.$interface;
        }
        $url .= '.'.$format ;
        if(!empty($pre_query)){
           $url .= "?$pre_query";
        }
        return $url;
     }
    
}
