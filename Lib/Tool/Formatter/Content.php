<?php
class Tool_Formatter_Content {
    
    public static function cut_mblog_text($content, $max, $etc = '...') {
        $result = $content;
        if(($rtn1 = Tool_Formatter_Content::deal_link($content, $max)) !== FALSE) {
            $result = $rtn1;
        } elseif (($rtn2 = Tool_Formatter_Content::deal_at($content, $max)) !== FALSE) {
            $result = $rtn2;
        } elseif (($rtn3 = Tool_Formatter_Content::deal_tag($content, $max)) !== FALSE) {
            $result = $rtn3;
        } else {
            $result = Tool_Formatter_Content::substr_cn($content, $max, 'UTF-8', '');
        }
        if (strlen($content) > strlen($result)) {
            return $result . $etc;
        } else {
            return $result;
        }
        return $result . $etc;
    }
    
    public static function deal_link($content, $cut) {
        //短链解析
        $grep = "!http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\]\`\>\%\>\/\?\:\@\&\=(\&amp\;)\#\|]+!is";	
        preg_match_all($grep, $content, $out);
        if (count($out[0]) == 0) {
            return false;
        }
        return self::deal_cut($content, $cut, $out[0]);
    }
    
    public static function deal_at($content, $cut) {
        //@信息
        $tools_analyze_at = new Tool_Analyze_At();
        $at_names = $tools_analyze_at->get_at_username($content);
        if (count($at_names) == 0) {
            return false;
        }
        foreach ($at_names as &$name) {
            $name = '@' . $name;
        }
        return self::deal_cut($content, $cut, $at_names);
    }
    
    public static function deal_tag($content, $cut) {
        //tag信息
        $tolls_analyze_tag = new Tool_Analyze_Tag();
        $tags = self::get_tags($content);
        
        if (count($tags) == 0) {
            return false;
        }
        
        foreach ($tags as &$tag) {
            $tag = '#' . $tag . '#';
        }
        return self::deal_cut($content, $cut, $tags);
    }
    
    /**
     * 截取内容函数
     * @param string $content 原字符串
     * @param int $cut
     * @param array $sch_strs 需要完成保留或忽略的字符串数组（数组顺序必须以在原字符串中出现的先后是顺序一致）
     */
    public static function deal_cut($content, $cut, $sch_strs) {
        $rtn = self::substr_cn($content, $cut, 'UTF-8', '');
        $cut_pos = strlen($rtn);
        foreach($sch_strs as $search_str) {
            $pos = strpos($content, $search_str);
            $end = $pos + strlen($search_str);
            if($cut_pos < $pos) {
                break;
            } elseif ($cut_pos > $end) {
                continue;
            } elseif($pos == $cut_pos || $end == $cut_pos) {
                return $rtn;
            } elseif($pos < $cut_pos && $end > $cut_pos) {
                return substr($content, 0, $pos);
            }
        }
        return false;
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
	public static function substr_cn($string, $length = 80, $charset = 'UTF-8', $etc = '...') {
		if(mb_strwidth($string,'UTF-8')<$length) return $string;
		return mb_strimwidth($string,0,$length,'',$charset) . $etc;
	}
	
	public static function get_tags($content) {
		$content = str_replace("＃", "#", $content);
		$ret = preg_match_all("/#([^#<>]+?)#/ise", $content, $tags);
		if(0 === $ret) {
			return array();
		} else {
			return array_unique($tags[1]);
		}
	}
	
	/**
	 * 转换字符串中的老域名成新域名　
	 * 
	 * @param string $string
	 */
	public static function change_new_domain($string) {
	    if (FALSE === strpos($string, 'http://t.sina.com.cn')) {
	        return $string;
	    }
	    return str_replace('http://t.sina.com.cn', Comm_Util::conf("domain.weibo"), $string);
	}
}