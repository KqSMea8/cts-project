<?php
/**
 * 投票接口请求类
 *
 * @package    request
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Comm_Weibo_Api_Request_Vote extends Comm_Weibo_Api_Request_Abstract{
    public static $vote_api_server_name = 'http://vote.i.t.sina.com.cn';
    public static $vote_version = 4;
    
    public function __construct($url,$method, $type){
        parent::__construct($url,$method);
        if(strtoupper($method) == "POST"){
            $this->http_request->add_post_field("type", $type);
            $this->http_request->add_post_field("version", self::$vote_version);
        }
        else{
        	$this->http_request->add_query_field("type", $type);
        	$this->http_request->add_query_field("version", self::$vote_version);
        }
    }
    
    /**
     * 投票接口URL拼接方法
     * @param string $resource 请求的资源（企业微博、微博、手机等）
     */
    public static function assemble_vote_api_url($resource) {
        $url = self::$vote_api_server_name . '/' . $resource;
        return $url;
    }
    
    public function get_rst(){
        parent::send();
        $content = $this->http_request->get_response_content();
        $http_code = $this->http_request->get_response_info('http_code');
        if ($http_code != '200' || empty($content)) {
            throw new Comm_Weibo_Exception_Api('Http Error:' . $http_code, $http_code);
        }
        $result = Comm_Util::json_decode($content);
        if ($result['code'] !== 'A00006') {
            $msg = isset($result['msg']) ? $result['msg'] : $result['error'];
            throw new Comm_Weibo_Exception_Api($msg, $result['code']);
        }
        return $result['data'];
    }
    
    /**
     * 添加当前用户uid规则的统一方法
     */
    public function support_cuid() {
        parent::add_rule("cuid","int",TRUE);
    }
    
    /**
     * 添加来源规则的统一方法
     */
    public function support_from() {
        parent::add_rule("from", "string", false);
    }
}