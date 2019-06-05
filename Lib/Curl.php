<?php

/**
 * CURL 工具
 *
 */

class Lib_Curl {
    public $url;
    public $method;

    public $query_fileds;
    public $post_fileds;

    public $ch = null;
    private $is_ssl = false;
    private $certificate_url = '';
    private $timeout = 3;
    private $connect_timeout = 3;

    const RETURN_FORMAT_JSON = 0;
    const RETURN_FORMAT_STRING = 1;


    public function get($url, $query_fileds, $return_format = self::RETURN_FORMAT_JSON, $timeout = 3) {
        $this->method = 'GET';
        $this->query_fileds = $query_fileds;
        $this->post_fileds = array();
        $this->setUrl($url);
        $this->timeout = $timeout;

        $response = $this->request();

        if ($return_format == self::RETURN_FORMAT_JSON) {
            return json_decode($response, true);
        } else {
            return $response;
        }
    }
    //fix
    public function post($url, $post_fileds, $return_format = self::RETURN_FORMAT_JSON, $timeout = 3) {
        $this->method = 'POST';
        //$this->query_fileds = $query_fileds;
        $this->post_fileds = $post_fileds;
        $this->setUrl($url);
        $this->timeout = $timeout;

        $response = $this->request();

        if ($return_format == self::RETURN_FORMAT_JSON) {
            return json_decode($response, true);
        } else {
            return $response;
        }
    }




    private function request() {
        $this->ch = curl_init($this->url);
        $this->setOption();
        $content = curl_exec($this->ch);
        //请求内容返回空
        if($content == false){
            Lib_Log::warning("curl get empty content. curl info:".@json_encode(curl_getinfo($this->ch)));
        }
        //curl请求出错
        if(curl_errno($this->ch) !== 0) {
            Lib_Log::warning("curl error occurred. error message:".curl_error($this->ch));
        }
        curl_close($this->ch);
        return $content;
    }


    private function setUrl($url, $certificate_url = '') {
        $this->url = $url;
        $url_element = parse_url($url);
        if($url_element["scheme"] == "https") {
            $this->is_ssl = true;
            $this->certificate_url = $certificate_url;
        } elseif ($url_element["scheme"] != "http") {
            $this->is_ssl = false;
        }

    }

    private function setOption() {
        //过滤HTTP头
        curl_setopt($this->ch, CURLOPT_HEADER, 0 );

        //显示输出结果
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);

        //目前没有证书，无须做相关验证
        if(true === $this->is_ssl) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            if($this->certificate_url != '') {
                curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);            //SSL证书认证
                curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);               //严格认证
                curl_setopt($this->ch, CURLOPT_CAINFO, $this->certificate_url);  //证书地址
            }
        }

        //设置请求方式
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->method);


        //$post_string = implode("&", $this->post_fileds);

        if ($this->method === 'POST') {
            curl_setopt($this->ch, CURLOPT_POST, true );
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post_fileds);
        }elseif($this->method === 'GET'){
            $query_string = http_build_query($this->query_fileds);
            curl_setopt($this->ch, CURLOPT_URL, $this->url . '&' . $query_string);
        }

        //设置超时时间
        curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,$this->connect_timeout);
        curl_setopt($this->ch,CURLOPT_TIMEOUT,$this->timeout);
    }
}