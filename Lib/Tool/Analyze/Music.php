<?php
/**
 * 
 * Enter description here ...
 * @author hongxue<hongxue@staff.sina.com.cn>
 *
 */
class Tool_Analyze_Music {
    //合作方定义
    public static $music_source = array("sina"=>array(
        	'cname'=>'新浪乐库',
    		'api_url' => 'http://music.sina.com.cn/yueku/port/sina_t_getsonginfo.php?url=%s',//获取扩展信息接口
    		'pattern' => array('http://music.sina.com.cn/yueku/i/'),
            'appkey'  => '',
            ) // 音乐地址识别规则	
    	,"xiami"=>array(
        	'cname'   =>'虾米网',
    		'api_url' => 'http://www.xiami.com/app/nineteen/realinfo?url=%s',
    	 	'appkey'  =>'3845915579',	//除新浪乐库外,其余第三方均需要appkey字段信息
    		'pattern' => array('http://www.xiami.com/song/')
    	),
    	"songtaste"=>array(
        	'cname'   =>'songtaste',
    		'api_url' => 'http://api.songtaste.com/songinfo?url=%s',
    	 	'appkey'  =>'2849783130',	
    		'pattern' => array('songtaste.com/song/') 	
    	));
    	
    /**
     * 取得第三方mp3信息
     * 
     * @param string $url
     * @param string $type
     * @return array
     */
    public function get_thd_id3($url, $type){
        if (!isset(self::$music_source[$type])) {
            throw new Comm_Exception_Program("unknow_music_url");
        }
        
        $url = sprintf(self::$music_source[$type]['api_url'], $url);
        $http_request = new Comm_HttpRequest($url);
        $http_request->send();
        $response = $http_request->get_response_content();
        if (!$response) {
            throw new Comm_Exception_Program("error_music_url");
        }
        
        $response = json_decode($response, true);
        //不可识别的地址
        if ($response['stat'] == 2) {
            throw new Comm_Exception_Program('url can\'t identify');
        }
        
        if (!isset($response['result']) || empty($response['result'])) {
            throw new Comm_Exception_Program('api_result_error');
        }
        
        $result = array();
        $result['url'] = $url;
        $result['from'] = $type;
        $result['artist'] = urldecode($response['result']['singer']);
        $result['title'] = isset($response['result']['song']) ? urldecode($response['result']['song']) : urldecode($response['result']['title']);
        $result['album'] = isset($response['result']['album']) ? urldecode($response['result']['album']) : '';
        $result['appkey'] = self::$music_source[$type]['appkey'];
        $result['gender'] = isset($result['gender']) ? $result['gender'] : '';
        return $result;
    }
    
    /**
     * 根据音乐url确定音乐来源
     * 
     * @param string $url
     * @return array|bool
     */
    public function get_types($url) {
        if (empty($url)) {
            return FALSE;
        }
        $result = FALSE;
        foreach (self::$music_source as $k => $v) {
            $pattern = '#'.join($v['pattern']).'#i';
            if (preg_match($pattern, $url)) {
                $result = array('from' => $k, 'url' => $url, 'appkey' => $v['appkey']);
                break;
            }
        }
        return $result;
    }
    
    public static function parse_id3($url) {
        $http_request = new Comm_HttpRequest($url);
        $http_request->no_body = TRUE;
        $http_request->send();
        $info = array();
        $res_info = $http_request->get_response_info();
        
        if ($res_info['http_code']) {
            //清空$curl_state，使用新创建curl_init发送请求
            Comm_HttpRequestPool::$curl_state = array();
            $http_request->no_body = FALSE;
            $http_request->set_request_range(0, 119);
            $http_request->send();
            $line = $http_request->get_response_content();
            
            $info = self::get_id3_header($line);
            $total_size = $res_info['download_content_length'];
            
            if ($total_size > 0 && $info == FALSE) {
                $http_request->set_request_range($total_size - 128, $total_size);
                $http_request->send();
                $line = $http_request->get_response_content();
                
                if (!empty($line)) {
                    if (preg_match("/^TAG/", $line)) {
                        $info = unpack("a3tag/a30title/a30artist/a30album/a4year/a30comment/C1gender", $line);
                    } else if (preg_match("/^ID3/", $line)) {
                        $info = self::get_id3_header($line);
                    }
                }
            }
        }
        
        if ($info == array() || $info == FALSE) {
            throw new Comm_Exception_Program("error_music");
        }
        
        foreach ($info as &$v) {
            $v = htmlspecialchars(mb_convert_encoding(str_replace("\000", "", $v), 'UTF-8', 'GBK'));
        }
        return $info;
    }
    
    public static function get_frame_size($fourBytes) {
        $tamanho[0] = str_pad(base_convert(substr($fourBytes, 0, 2), 16, 2), 7, 0, STR_PAD_LEFT);
        $tamanho[1] = str_pad(base_convert(substr($fourBytes, 2, 2), 16, 2), 7, 0, STR_PAD_LEFT);
        $tamanho[2] = str_pad(base_convert(substr($fourBytes, 4, 2), 16, 2), 7, 0, STR_PAD_LEFT);
        $tamanho[3] = str_pad(base_convert(substr($fourBytes, 6, 2), 16, 2), 7, 0, STR_PAD_LEFT);
        $total = $tamanho[0] . $tamanho[1] . $tamanho[2] . $tamanho[3];
        $tamanho[0] = substr($total, 0, 8);
        $tamanho[1] = substr($total, 8, 8);
        $tamanho[2] = substr($total, 16, 8);
        $tamanho[3] = substr($total, 24, 8);
        $total = $tamanho[0] . $tamanho[1] . $tamanho[2] . $tamanho[3];
        $total = base_convert($total, 2, 10);
        return $total;
    }
    
    public static function extract_tags($text) {
        $tags = array();
        $size = -1; //inicializando diferente de zero para não sair do while
        while ((strlen($text) != 0) and ($size != 0)) {
            //while there are tags to read and they have a meaning
            //while existem tags a serem tratadas e essas tags tem conteudo
            $ID = substr($text, 0, 4);
            $aux = substr($text, 4, 4);
            $aux = bin2hex($aux);
            $size = self::get_frame_size($aux);
            $flags = substr($text, 8, 2);
            $info = substr($text, 11, $size - 1);
            if ($size != 0) {
                $tags[$ID] = $info;
            }
            $text = substr($text, 10 + $size, strlen($text));
        }
        return $tags != array() ? $tags : false;
    }
    
    /**Read the file and put the TAGS
     **/
    public static function get_id3_header($mp3) {
        $header = substr($mp3, 0, 10);
        $header = bin2hex($header);
        $version = base_convert(substr($header, 6, 2), 16, 10) . "." . base_convert(substr($header, 8, 2), 16, 10);
        $flags = base_convert(substr($header, 10, 2), 16, 2);
        $flags = str_pad($flags, 8, 0, STR_PAD_LEFT);
        if ($flags[7] == 1) {
            //echo('with Unsynchronisation<br>');
        }
        if ($flags[6] == 1) {
            //echo('with Extended header<br>');
        }
        if ($flags[5] == 1) { //Esperimental tag
            return false;
        }
        $total = self::get_frame_size(substr($header, 12, 8));
        $text = substr($mp3, 10, $total);
        $tags = self::extract_tags($text);
        if ($tags != false) {
            $info['artist'] = $tags['TPE1'];
            $info['title'] = $tags['TIT2'];
            $info['album'] = $tags['TALB'];
            $info['year'] = $tags['TYER'];
            $info['gender'] = $tags['TCON'];
            return $info;
        }
        return false;
    }
}