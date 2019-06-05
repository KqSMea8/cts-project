<?php
/**
 * 字符串格式化工具
 *
 * @package    Tool
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     zhangbo<zhangbo@staff.sina.com.cn>
 */
class Tool_Formatter_String {
	/**
	 * 去掉字符串前后的空格(半全角空格)
	 *
	 * @param string $str
	 * return 处理后的文本
	 */
	static public function trim_cn($str) {
        $str = ' '.$str;
        return preg_replace('/(^[\s\x{3000}]*)|([\s\x{3000}]*$)/u','',$str);
	}
	
	/**
	 * 替换所有的全半角空格
	 * Enter description here ...
	 * @param unknown_type $str
	 */
	static public function trim_all($str){
        $str = str_replace(array("　","\n","\r"), " ", $str);
        $str = preg_replace("/[ ]{1,}/", " ", $str);
        $str = str_replace('＠','@',$str);
        return $str;      
	}
	
	/**
	 * 中英文混杂字符串截取
	 *
	 * @param string $string
	 * 原字符串
	 * @param interger $length
	 * 截取的字符数
	 * @param string $etc
	 * 省略字符
	 * @param string $charset
	 * 原字符串的编码
	 * 
	 * @return string
	 */
	static public function substr_cn($string, $length = 80, $charset = 'UTF-8', $etc = '...') {
		if(mb_strwidth($string,'UTF-8')<$length) return $string;
		return mb_strimwidth($string,0,$length,'',$charset) . $etc;
	}
    
    /**
         * 中英文混杂字符串截取,截取后总长不超过$length
         *
         * @param string $string
         * 原字符串
         * @param interger $length
         * 截取的字符数
         * @param string $etc
         * 省略字符
         * @param string $charset
         * 原字符串的编码
         * 
         * @return string
         */
        static public function substr_cn2($string, $length = 80, $charset = 'UTF-8', $etc = '...') {
        		if(mb_strwidth($string,'UTF-8')<$length) return $string;
                return mb_strimwidth($string,0,$length,$etc,$charset);
        }
	
	/**
	 * 中英文混杂字符串截取
	 * @param string $string 原字符串
	 * @param interger $length 截取字数(中英各代表一个字)
	 * @param string $charset 编码
	 */
	static public function split_str2array_cn($string, $length = 80, $charset = 'UTF-8') {
		$i = $k = $nextstart = 0;
	
		$split_count = ceil(strlen($string)/($length*2));		
		for($ii=0;$ii<$split_count;$ii++) {
			$en = $cn = 0;
			while($k < $length) {
				if (preg_match ("/[0-9a-zA-Z]/", $string[$i])){  
		            $en++;   	//纯英文 
		        }   
		    	else {
		    		$cn++;     //非英文字节
		    	}
				$k = $cn/3+$en/2;
		        $i++;   
			}		 
			
			$split_len += $cn/3+$en;	//最终截取长度	
		    $start = $nextstart;
		    $nextstart += floor($split_len);	
			$tmpstr = mb_substr($string,$start,$split_len,$charset);
			if(!empty($tmpstr)) {
				$split_array[] = mb_substr($string,$start,$split_len,$charset);
			}
		}
	    return $split_array;	
	}
	/**
	 * 
	 * 截取字符串到固定长度，并补全“...”
	 * @param unknown_type $content
	 * @param unknown_type $length
	 */
    static public function content_truncate($content, $length, $charset = 'UTF-8', $etc = '...', &$show_title=FALSE){
		$utf_width = mb_strwidth($content, $charset);
        $real_width = (strlen($content) + mb_strlen($content,$charset)) / 2;
		if($real_width > $length + 2){
			$get_width = $length;
			if(($utf_width-1)*2<=$real_width) $get_width = $get_width/2;	//特殊字符截取的长度
			$content = mb_strimwidth($content, 0, $get_width, "", $charset).$etc;
			$show_title = true;
		}
		return $content;
    }
    //来源处理
    
    static public function location_truncate($content, $length = 30){
    	$source_content = $long_content = strip_tags($content);
    	$show_title = false;
    	$truncate_content = Tool_Formatter_String::content_truncate($long_content, $length, 'UTF-8', '...', $show_title);
    	$content = str_replace($long_content, $truncate_content, $content);
    	if($show_title){		
    		$content = str_replace('href', " title={$source_content} href", $content);
    	}
    	return $content;
    }
	/**
	 * 对传入的内容标红处理，可能存在多个关键字需要标红则循环处理
	 *
	 * @param string $content  内容
		 * @param   string  $searKey  标红的对象
		 * @return string
	 */
	static public function red_tag($content, $sear_key)
	{
		if (in_array($sear_key, array('~', '/'))) {
			$sear_key_arr = array($sear_key);
		}
		else {
			$sear_key_arr = array_unique(preg_split("/[\s|\/|~]+/", $sear_key));
		}
		
		if (count($sear_key_arr) > 0) {
			foreach ($sear_key_arr as $v) {
				$vv = $v;
				if($vv === true) continue ;
				//过滤转义特殊字符
				$v_word = array('+', '.', '?', '$', '^', '*', '(', ')', '[', ']', '{', '}');
				foreach ($v_word as $vm) {$vv = str_replace($vm, "\\{$vm}", $vv);}
				$content = self::deal_red_tag($content, $vv);
			}
		}
		return $content;
	}
	/**
	 * 
	 * 昵称标红
	 * @param $params
	 */
	static public function name_red_tag($params){
		if (empty($params['name']) || empty($params['key_word'])) {
			return $params['name'];
		}
		$name = $params['name'];
		$key_word = $params['key_word'];
		if (preg_match("/($key_word)/i", $name)){
			$name = preg_replace("/($key_word)/i", "<span style='color: red;'>\\1</span>", $name);
		}
		return $name;
	}
    /**
	 * 标红处理
	 *
	 * @param string $content  内容
		 * @param   string  $searKey  标红的对象
		 * @return string
	 */
	static public function deal_red_tag($content, $preg_key) 
	{
		if (empty($content) || empty($preg_key)) {return $content;}
		$html_tags = array();
		preg_match_all("/<(\S*?)[^>]*>.*?<\/\\1>|<[^>]+>|<sina:link[^>]*>/i", $content, $tmps);
		
		foreach($tmps[0] as $k => $v) {
			array_push($html_tags, array('sTag' => "#tag{$k}#", 'oTag' => $v));
		}
		if (count($html_tags) > 0) {
			foreach ($html_tags as $ht) {
				$content = str_replace($ht['oTag'], $ht['sTag'], $content);
			}
		}
		$content = preg_replace("/($preg_key)/i", "<span style='color: red;'>\\1</span>", $content);
		$content = str_replace("＃", "#", $content);
		$content = preg_replace("/#([^#]+)#/ies", "strip_tags('#\\1#')", $content);
		if (count($html_tags) > 0) {
			foreach ($html_tags as $ht) {
				$content = str_replace($ht['sTag'], $ht['oTag'], $content);
			}
		}
		return $content;
	}

}