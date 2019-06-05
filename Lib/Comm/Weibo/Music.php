<?php
/**
 * 音乐搜索接口操作类
 *
 * @package    Comm
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     hqlong <qinglong@staff.sina.com.cn>
 *             hongxue <hongxue@staff.sina.com.cn>
 * @version    2011-4-26
 */

class Comm_Weibo_Music {
    //sina乐库接口
    //联想搜索
    const RECOMMEND_URL = "http://i.music.sina.com.cn/yueku/search/getRecommendXml1dot0.php?q=%s"; 
     //搜索sina乐库的歌曲
    const SEARCH_URL = "http://i.music.sina.com.cn/yueku/intro/musina_mmi_search.php?key=%s&start=0&limit=5";
    //sina乐库歌曲的详细信息
    const MUSIC_INFO = "http://i.music.sina.com.cn/yueku/intro/musina_mmi_playlist.php?coFlag=200006";
    const MUSIC_LOGO = "http://i.music.sina.com.cn/yueku/port/playlog.php?id=%d&name=%s&playlength=%s&totallength=%s&coFlag=200006&ownerid=%d"; 
    
    /**
     * 获取GET请求的响应内容
     * 
     * @param string $url
     * @param BOOL $is_raw_url 是否直接使用原始url，此参数解决GET参数传递数组的情况，如id[]=xxx&id[]=yyy
     * @return mixed
     */
    public function get_response_result($url, $is_raw_url = FALSE) {
        $request = new Comm_HttpRequest();
        if ($is_raw_url === FALSE) {
            $request->set_url($url);
        } else {
            $request->url = $url;
        }
        $request->send();
        return $request->get_response_content();
    }

    /**
     * mp3关键字联想
     * 
     * @param string $keyword
     * @return string|string|string|multitype:
     */
    public function suggest($keyword) {
        if (empty($keyword)) {
            throw new Comm_Exception_Program('argument $keyword must not empty');
        }
        
        $url = sprintf(self::RECOMMEND_URL, $keyword);
        $response = $this->get_response_result($url);
        if (!$response) {
            return FALSE;
        }
        
        preg_match_all('/<search_key><!\[CDATA\[(.*)\]\]>/isU', $response, $matches);
        
        $musics = isset($matches[1]) ? $matches[1] : array();
                
        $result = array();
        foreach($musics as $k => $v) {
            $result[]['title'] = mb_convert_encoding($v, 'UTF-8', 'GBk');
        }
        return $result;
    }
    
    /**
     * mp3关键词搜索
     * 
     * @param string $keyword
     * @return mixed
     */
    public function search($keyword) {
        if (empty($keyword)) {
            throw new Comm_Exception_Program('argument $keyword must be not empty');
        }

        $url = sprintf(self::SEARCH_URL, urlencode($keyword));
        $response = $this->get_response_result($url);
        if (!$response) {
            return FALSE;
        }

        $response = json_decode($response, true);
        if (!$response) {
            return FALSE;
        }
        
        $song_list = isset($response['result']['songlist']) ? $response['result']['songlist'] : array();
        $result = array();
        foreach ($song_list as $k => $v) {
            $item = array();
            $item['title']  = htmlspecialchars($v['NAME'], ENT_QUOTES);
            $item['artist'] = htmlspecialchars($v['SINGERCNAME'], ENT_QUOTES);
            $item['album']  = htmlspecialchars($v['ALBUMCNAME'], ENT_QUOTES);
            $item['mp3_id'] = $v['SONGBASEID'];
            $result[$item['mp3_id']] = $item;
        }
        return $result;
    }
    
    /**
     * 根据muser_id获取音乐详细信息
     * 
     * @param int $music_id
     * @return mixed
     */
    public function get_sinamusic_info($music_id) {
        if (empty($music_id)) {
            throw new Comm_Exception_Program('argument $$music_id must be not empty');
        }
        
        $url = self::MUSIC_INFO."&id[]=$music_id";
        $response = $this->get_response_result($url, TRUE);
        if (!$response) {
            return FALSE;
        }
        $response = json_decode($response, true);
        if (!isset($response['result'][0]['NAME'])) {
            return array();
        }
        
        $result = array();
        $result['title'] = $response['result'][0]['NAME'];
        $result['artist'] = $response['result'][0]['SINGERCNAME'];
        $result['mp3_url'] = $response['result'][0]['MP3_URL'];
        $result['mp3_id'] = $response['result'][0]['SONGBASEID'];
        return $result;
    }
    
    /**
     * 批量取得音乐信息
     * 
     * @param $music_ids
     */
    public function get_sinamusic_infos(Array $music_ids) {
        if(empty($music_ids)) {
            throw new Comm_Exception_Program('argument $music_ids must be not empty');
        }
        $music_ids = array_map(create_function('$a', 'return "id[]=$a";'), $music_ids);
        $url = self::MUSIC_INFO."&".implode("&", $music_ids);
        
        $response = $this->get_response_result($url, TRUE);
        $response = json_decode($response, true);
        $response = isset($response['result']) ? $response['result'] : array();
        $result = array();
        foreach ($response as $v) {
            $item = array();
            $item['title'] = $v['NAME'];
            $item['artist'] = $v['SINGERCNAME'];
            $item['album'] = $v['ALBUMCNAME'];
            $item['mp3_url'] = $v['MP3_URL'];
            $item['mp3_id'] = $v['SONGBASEID'];
            $result[$item['mp3_id']] = $item;
        }
        return $result;
    }
}
