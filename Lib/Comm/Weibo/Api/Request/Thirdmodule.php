<?php
/**
 * 第三方模块请求类
 *
 * @package    
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     zhangbo <zhangbo@staff.sina.com.cn>
 * @version    2011-10-11
 */
class Comm_Weibo_Api_Request_ThirdModule extends Comm_Weibo_Api_Request_Abstract {
    const RST_SUCCESS_CODE = 100000;
 	static public $check_succ_ret = true;
    static public $check_json_decode = true;
    
    static public function set_check_succ_ret($check_succ_ret = true) {
    	self::$check_succ_ret = $check_succ_ret;
    }

    static public function set_check_json_decode($check_json_decode = true) {
        self::$check_json_decode = $check_json_decode;
    }
    /**
     * 接口请求方法
     * @see Comm_Weibo_Api_Request_Abstract::get_rst()
     * @return 接口无异常时的正常返回值
     */
 public function get_rst() {
        parent::send();
        try{
        $content = $this->http_request->get_response_content();
        if (mb_check_encoding($content, 'GBK')) {
            $content = iconv('GBK', 'UTF8', $content);
        }

        if (self::$check_json_decode) {
            $result = Comm_Util::json_decode($content, true);
        } else {
            return $content;
        }

        $exp_msg = $exp_code = FALSE;
        if ($this->http_request->get_response_info('http_code') != '200') {
            if(isset($result['error'])){
                $exp_msg = $result['error'];
                $exp_code = $result['error_code'];
                //$logs_type = Comm_Log_Weibolog::ERROR_API;
                $logs_type = 'API_ERROR';
                $logs_ext = $result;
            }
            else{
                $exp_msg = "http error:".$this->http_request->get_response_info('http_code');
                $exp_code = $this->http_request->get_response_info('http_code');
                //$logs_type = Comm_Log_Weibolog::SYSERR;
                $logs_type = 'SYSERR';
                $logs_ext = array('err_msg' => $exp_msg);
            }
            throw new Comm_Weibo_Exception_Api($exp_msg,$exp_code);
        }elseif(!is_array($result)){
                $exp_msg = "api return data can not be json_decode";
                $exp_code = -1;
                //$logs_type = Comm_Log_Weibolog::INFO;
                $logs_type = 'INFO';
                $logs_ext = array('err_msg' => $exp_msg);
                throw new Comm_Weibo_Exception_Api($exp_msg,$exp_code);
        } else{
        	if(self::$check_succ_ret) {
	            if(isset($result['code']) && isset($result['data']) && isset($result['msg'])) {
	            	if($result['code'] != self::RST_SUCCESS_CODE) {
 						$exp_code = $result['code'];
                        $exp_msg = $result['msg'];
                        //$logs_type = Comm_Log_Weibolog::ERROR_API;
                        $logs_type = 'ERROR_API';
                        $logs_ext = $result;
	                    throw new Comm_Weibo_Exception_Api($exp_msg,$exp_code);
	                }
	            } else {
	                $exp_code = -1;
	                $exp_msg = 'wrong data type!';
	                $logs_type = 'INFO';
	                $logs_ext = $result;
	                throw new Comm_Weibo_Exception_Api($exp_msg,$exp_code);
	            }
        	} 
         }
     	}catch (Comm_Weibo_Exception_Api $e){
     		//Comm_Weibo_Api_Request_Log::write_error_log($this->http_request, $logs_type, $logs_ext);
        	//Comm_Log_Weibolog::write_openapi_log_to_scribe($this->http_request, $logs_type, $logs_ext);
            Tool_Log::fatal($this->http_request, $logs_type, $logs_ext);
     		throw $e;
     	}
        if(in_array($_SERVER['SERVER_ADDR'],array('10.73.13.21','10.73.13.23','10.75.12.54','10.75.12.55'))){
	    	Comm_Weibo_Api_Request_Log::write_error_log($this->http_request, Comm_Weibo_Api_Request_Log::SUCCESS,array());
	    }
        return $result;  
    }
}