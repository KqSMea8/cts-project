<?php
class Comm_Weibo_Api_Request_IDotT extends Comm_Weibo_Api_Request_Abstract{
    public static $idott_api_server_name = "http://i.t.sina.com.cn";
    public static $idott_api_default_format = "json";
    private $uid = NULL;
    
    
    public function __construct($url,$method, $uid = NULL){
        if($uid == NULL) {
            if($_COOKIE['SUP']) {
                if(preg_match('/uid=([0-9]+)/', $_COOKIE['SUP'],$matches)) {
                    $uid = $matches[1];
                }
            }
        }
        if($uid) {
            $url .= '&uid=' . $uid;
        } else {
            throw new Comm_Exception_Program("i.t interface need uid");
        }
        parent::__construct($url,$method);
    }
    
    public function get_rst(){
        parent::send();
        $content = $this->http_request->get_response_content();
        $result = Comm_Util::json_decode($content);
        if ($this->http_request->get_response_info('http_code') != '200' || empty($content)) {
            throw new Comm_Weibo_Exception_Api($result['errmsg'],$result['errno']);
        }
           return $result;
    }
    
    public function support_uid(){
        parent::add_rule("uid","int64",TRUE);
    }
        
    public function support_get_page(){
        parent::add_rule("page", "int",FALSE);
        parent::add_rule("pagesize", "int",FALSE);
        parent::add_rule("sort", "int",FALSE);
    }
    
    /**
     * i.t下接口的拼接方法
     * @param string $resource 请求的资源
     * @param string $source 接口的来源（wap，msn，api）
     * @param string $interface 接口名称
     * @param int $uid 当前用户uid
     * @param string $cip 当前用户的ip地址
     */
    public static function assemble_url($resource,$source,$interface,$cip){
        if(empty($interface)){
                throw new Comm_Exception_Program("interface not exist");
        }
        if(empty($cip)){
                throw new Comm_Exception_Program("i.t interface need cip");
        }
        $appid = Comm_Util::conf("env.idott_api_appid");
        $query['cip'] = $cip;
        $query['appid'] = $appid;
        $url = self::$idott_api_server_name . "/" . (isset($source) ? "{$source}/" : "") . (isset($resource) ? "{$resource}/" : "") . $interface . ".php" . "?" . http_build_query($query);
        return $url;
    }
    
}