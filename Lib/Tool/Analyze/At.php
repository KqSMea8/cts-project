<?php
/**
 * 分析@符号
 * @package    Tool
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */

class Tool_Analyze_At
{
	//存储替换规则  '被替换字符串'=>'替换成字符串'
	var $strip_arr = array();
	const AT_LOCATION_SEARCH_URL = "/n/";
	/**
	 * 提取出@
	 *
	 * @param string $content
	 * @return array all the @username
	 */
	public function get_at_username($content) {
		$content = strip_tags($content);
		$content = $this->strip_email($content, false);
		$content = $this->mb_filter($content);
		$names = array();
		$ret = preg_match_all("/@([\x{4e00}-\x{9fa5}\x{ff00}-\x{ffff}\x{0800}-\x{4e00}\x{3130}-\x{318f}\x{ac00}-\x{d7a3}a-zA-Z0-9_\-]+)/u",$content,$names);
		if(!$ret) {
			return array();
		} else {
			$at_name = array_unique($names[1]);
			$at_name = array_combine($at_name, $at_name);
			foreach ($at_name as $key => $value) {
				if(preg_match("/^[0-9]{3,10}$/",$value)) { //解析微号
					try {
						$user_info = Dr_User::get_user_info_by_domain($value);
						if(isset($user_info['weihao']) && $user_info['weihao'] == $value) {
		        			$at_name[$key] = $user_info['screen_name'];
						}
					} catch (Comm_Weibo_Exception_Api $e) {
						$names[1] = array();
					}
				}
			}
			return $at_name;
		}
	}
	
	/*
	 * $content中将微号替换成昵称
	 */
	public function replace_weihao_to_nick($content, $at_name) {
		foreach ($at_name as $key => $value) {
			if(preg_match("/^[0-9]{3,10}$/",$key)) { 
				$content = str_replace($key, $value, $content);
			}
		}
		return $content;
	}

	//提取文本中的email地址
	public function get_email($str) {
		$pattern = "/[a-z0-9]([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i";
    	preg_match_all($pattern,$str,$email_arr);
    	return $email_arr[0];
	}

	//去除文本中的email地址
	public function strip_email($content, $rep_str=false) {
		$email_arr = $this->get_email($content);
		foreach($email_arr as $no => $email) {
			if($rep_str) {
				$rep_str = "reSinaEmail".$no;
				$this->strip_arr[$rep_str] = $email;
				$content = str_replace($email, $rep_str, $content);
			} else {
				$content = str_replace($email, "", $content);
			}
		}
		return $content;
	}

	public function at_to_link(&$content, $at_users, $is_target=false) {
		if(!is_array($at_users)) return;
		mb_internal_encoding("utf-8");
		//去除字符串中的email地址
		$content = $this->strip_email($content, true);
		//对要替换昵称长度排序
		usort($at_users, array('Tool_Analyze_At','sort_by_len'));
		$target_str = $is_target ? "target=\"_blank\"" : "";
		$domain_name =  Comm_Util::conf('domain.weibo');
		foreach ($at_users as $nick) {
   			$content = preg_replace('|(?!>.*)((<span[^>]+>)?@(<span[^>]+>)?(' . $nick . ')(</span>)?)(?![^<]*<\/)|e', "'<a href=\"$domain_name'.self::AT_LOCATION_SEARCH_URL . urlencode('\\4') . '\" usercard=\"name=\\4\" {$target_str}>\\1</a>'", $content);
		}
		//还原原始$content
		foreach($this->strip_arr as $pat => $re_str) {
			$content = mb_ereg_replace($pat, $re_str, $content);
		}

	}

	public function strip_minblog_tags(&$text) {
		mb_internal_encoding("utf-8");
		$pattern = ">#(.*?)#<\/a>";
		$result = array();
		preg_match_all($pattern, $text, $result);
		if(is_array($result) && count($result)>0) {
			$pa_re = $result[1];
			foreach($pa_re as $key => $value) {
				$pattern2 = "<[^>]*>";
				$content = mb_ereg_replace($pattern2, "", $value);
				$pa_re[$key] = $content;
			}
			$rep = $result[0];
			foreach($rep as $key => $ma) {
				$text = str_replace($ma, "#" . $pa_re[$key] . "#</a", $text);
			}
		}
	}
	/**
	 * 过滤掉标点符号
	 *
	 * @param string $str
	 * @return string the filtered string
	 */	
	public function mb_filter_punct($str){
		$str = $this->mb_filter($str);
		$str = str_replace(" ", "",$str);
		return $str;
	}
	/**
	 * 过滤掉字符串中的特殊字符
	 *
	 * @param string $str
	 * @return string the filtered string
	 */
	private function mb_filter($str) {
		mb_internal_encoding("utf-8");
		$filter = array("`","~","!","#","$","%","^","&","*","(",")","=","+","[","]","{","}","|","'",";",":","\"","?","/",">","<",",",".","｀","～","·","！","◎","＃","¥","％","※","×","（","）","＋","－","＝","§","÷","】","【","『","』","‘","’","“","”","；","：","？","、","》","。","《","，","／","＞","＜","｛","｝","＼");
		foreach($filter as $v) {
			$str = str_replace($v,' ',$str);
		}
		return $str;
	}
	
	public function sort_by_len($a, $b) {
   		if (strlen($a) == strlen($b)) return 0;
   		return (strlen($a) < strlen($b)) ? 1 : - 1;
	}
}


