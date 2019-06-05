<?php
/**
 * 分析tag
 * @package    Tool
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
class Tool_Analyze_Tag
{
	/**
	 * 渲染tag显示
	 *
	 * @param string $content
	 * @return string
	 */
	public static function render_tag($content, $is_target=false) {
		$is_target = $is_target ? 1 : 0;
		$content = str_replace("＃", "#", $content);
		$content = str_replace ( '&#039;', '\'', $content );
		$content = str_replace ( '&#39;', '\'', $content );
		$str = preg_replace("/#([^#<>\"]+)#/ise", "self::strip_tag('\\1','\\0', {$is_target})", $content);
		return $str;
	}
	
	public static function strip_tag($str, $link_word, $is_target=false) {
		$str = trim($str);
		if($str == ""){
			return "##";
		}
		
		$target = $is_target ? ' target="_blank"' : '';
		$str = strip_tags($str);
		$link_word = strip_tags($link_word);
	    //增大字符的长度 避免用户点击话题出现问题
		if(mb_strwidth($str) > 80) {
	        $str = mb_strimwidth($str, 0, 80,'','UTF-8');
	    }
	    $huati_url = Comm_Config::get('domain.huati');
		$url = sprintf($huati_url, urlencode(htmlspecialchars_decode($str)));
		$str = '<a class="a_topic" href="'.$url.'"'.$target.'>'.$link_word.'</a>';
		return $str;
	}
    /**
     * user_tag_to_link 
     * 
     * 用户标签转化为连接
     * @param string $content  用户标签格式为[TAG]XX,XX[TAG]
     * @access public
     * @return void
     */
    public static function user_tag_to_link($content) {
        $out = array();
        preg_match_all("/\[TAG\](.*?)\[TAG\]/is", $content, $out);
        if(empty($out[0]) || !is_array($out[0]) || empty($out[1][0])) return $content;

        $search_domain = Comm_Util::conf('domain.search').'/user/&tag=%s';
        foreach($out[1] as $kk=>$tag) {
	        $source_str = $out[0][$kk];
	        $url = sprintf($search_domain, urlencode(urlencode(htmlspecialchars_decode($tag))));
	    	$replace_str = '<a href="'.$url.'">'.$tag.'</a>、'; 
	    	$replace_str = mb_substr($replace_str, 0, -1, 'UTF-8');
	        $content = str_replace($source_str, $replace_str, $content);
		}
        return $content;
    }		
}

