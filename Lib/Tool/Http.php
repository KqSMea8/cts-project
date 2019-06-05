<?php
class Tool_Http
{
    public static function call_http_service_by_url($url,$data = array(),$read_time_out = 10,$method = 'get',$result_format='json')
    { 
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Weibo.com Swift framework HttpRequest class");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, round(1, 3));
        curl_setopt($ch, CURLOPT_TIMEOUT, intval($read_time_out));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        $data['appkey'] = Comm_Config::get('env.platform_api_source');
        $data['logid'] = Comm_Log::get_logid();
        $method = strtolower($method);
        if ($method == 'post')
        {
            $query_string = http_build_query($data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string); // Content-Type will be multipart/form-data
        }
        else
        {
            if (!empty($data)){
                $query_string = http_build_query($data);
            }else{
                $query_string = '';
            }
            if (strpos($url,'?') === false){
                $url .= '?'.$query_string;
            }else{
                $url .= '&'.$query_string;
            }
            
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
  
        $logdata = $data;
        foreach ($logdata as &$v){
            if (mb_strlen($v,'utf-8') > 30){
                $v = mb_strimwidth($v,0,30,'...','utf-8');
            }
        }
        $strlogdata = serialize($logdata);
        $ret = curl_exec($ch);
        if ($ret == false){
            $error = curl_error($ch);
            $msg = sprintf('[httprequest]request error[url:%s][data:%s][error:%s]',$url,$strlogdata,$error);
            Comm_Log::warning($msg);
            throw new Dr_Exception($msg);
        }
        $result = $ret;
        if ($result == ''){
            $msg = sprintf('[httprequest]result empty[url:%s][data:%s]',$url,$strlogdata);
            Comm_Log::warning($msg);
            throw new Dr_Exception($msg);
        }
        $msg = sprintf('[httprequest]request success[url:%s][data:%s]',$url,$strlogdata);
        Tool_Log_Commlog::write_log('DEBUG',$msg);
        if ($result_format == 'json'){
            $arr = json_decode($result,true);
            if ($arr == null || !is_array($arr)){
                $msg = sprintf('[httprequest]invalid json result[url:%s][data:%s][result:%s]',$url,serialize($data),serialize($result));
                Comm_Log::warning($msg);
                throw new Dr_Exception($msg);;
            }
            return $arr;
        }
        curl_close($ch);
        return $result;
    }    
    
    /**
     * 通过curl发送HTTP请求
     * @param string $url 接口url
     * @param array $params 接口参数
     * @param int $timeout curl允许执行的最长秒数
     * @param string $method 请求类型 GET|POST
     * @param string $result_format 该方法返回的数据格式，默认为json格式
     * @param array $extheaders 扩展的包头信息（如：tauth2_header等）
     * @param bool $urlencode 请求类型为POST时是否对$params进行urlencode
     * @param string $auth_basic 使用的HTTP验证方法（该字段为空字符串表示不使用HTTP验证方法，目前仅绑定app时需传入该参数）
     * @return array
     */
    public static function http_request( $url , $params = array(), $timeout = 2, $method = 'GET', $result_format='json', $extheaders = array(), $urlencode = true, $auth_basic = '' ) {
        if ( !function_exists('curl_init') ) {
            exit('Need to open the curl extension');
        }
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        
        if (!empty($auth_basic)) { 
            curl_setopt($ci, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ci, CURLOPT_USERPWD, $auth_basic);
        }
        
        $method = !empty($method) ? strtoupper($method) : 'GET';
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if ( !empty($params) ) {
                    curl_setopt( $ci, CURLOPT_POSTFIELDS, $urlencode ? http_build_query($params) : $params );
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ( !empty($params) ) {
                    $url = $url . ( strpos($url, '?') ? '&' : '?' )
                                . ( is_array($params) ? http_build_query($params) : $params );
                }
                break;
        }
        
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        
        $headers = (array)$extheaders;
        if($headers) {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }
        
        $response = curl_exec($ci);
        if ( $result_format == 'json' ) {
            $arr = json_decode($response, true);
            if ( $arr == null || !is_array($arr) ) {
                $msg = sprintf('[httprequest]invalid json result[url:%s][data:%s][result:%s]',$url,serialize($params),serialize($response));
                Comm_Log::warning($msg);
                return false;
            }
            return $arr;
        }
        curl_close ($ci);
        return $response;
    }
}