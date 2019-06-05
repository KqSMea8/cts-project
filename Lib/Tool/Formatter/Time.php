<?php
/**
 * 分析时间
 * @package    Tool
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author     weibo.com php team
 */
if(Comm_I18n::$current_lang == 'zh-tw') {
	define("TIME_FORMAT_SECONDTE", "%s秒前");
	define("TIME_FORMAT_MINITE", "%s分鐘前");
	define("TIME_FORMAT_HOUR", "%s小時前");
	define("TIME_FORMAT_DAY", "%s天前");
	define("TIME_FORMAT_WEEK", "1周前");
	define("TIME_FORMAT_TODAY", "今天 %s");
	define("TIME_FORMAT_HISTORY", "%s-%s-%s");
	define("TIME_FORMAT_HISTORY_VISITOR", "%s月%s日");
	define('TIME_FORMAT_EVENT_NOYEAR', "%s月%s日 周%s %s");
	define('TIME_FORMAT_EVENT_WITHYEAR', "%s年%s月%s日 周%s %s");
} else {
	define("TIME_FORMAT_SECONDTE", "%s秒前");
	define("TIME_FORMAT_MINITE", "%s分钟前");
	define("TIME_FORMAT_HOUR", "%s小时前");
	define("TIME_FORMAT_DAY", "%s天前");
	define("TIME_FORMAT_WEEK", "1周前");
	define("TIME_FORMAT_TODAY", "今天 %s");
	define("TIME_FORMAT_HISTORY", "%s-%s-%s");
	define("TIME_FORMAT_HISTORY_VISITOR", "%s月%s日");
	define('TIME_FORMAT_EVENT_NOYEAR', "%s月%s日 周%s %s");
	define('TIME_FORMAT_EVENT_WITHYEAR', "%s年%s月%s日 周%s %s");
}

class Tool_Formatter_Time {

	public static function timeFormat($time) {
		$now = time();
		if (!is_numeric($time)) {
			$time = strtotime($time);
		}
		if(($dur = $now - $time) < 3600) {
			if($dur<50){
				$second = ceil($dur / 10)*10;
				if ($second<=0){
					$second = 10;
				}
				$time = sprintf(TIME_FORMAT_SECONDTE, $second);
			}else{
				$minutes = ceil($dur / 60);
				if ($minutes<=0){
					$minutes = 1;
				}
				$time = sprintf(TIME_FORMAT_MINITE, $minutes);
			}
		}elseif(date("Ymd", $now) == date("Ymd", $time)) {
			$time = sprintf(TIME_FORMAT_TODAY, date("H:i", $time));
		}else{
			
			if(date("Y") == date("Y",$time)){
				$time = sprintf(TIME_FORMAT_HISTORY_VISITOR,date("n",$time),date("j",$time)) . " " . date("H:i",$time);
			}else{
				$time = sprintf(TIME_FORMAT_HISTORY, date("Y",$time),date("n",$time),date("j",$time)) . " " . date("H:i",$time);
			}
		}
		return $time;
	}
	
	public static function timeFormatForKeywords($time) {
		$now = time();
		if (!is_numeric($time)) {
			$time = strtotime($time);
		}
		if(date("Y") == date("Y",$time)){
			$time = sprintf(TIME_FORMAT_HISTORY_VISITOR,date("n",$time),date("j",$time)) . " " . date("H:i",$time);
		}else{
			$time = sprintf(TIME_FORMAT_HISTORY, date("Y",$time),date("n",$time),date("j",$time)) . " " . date("H:i",$time);
		}
		return $time;
	}
	
	public static function timeFormatForMyapp($time) {
		$now = time();
		if (!is_numeric($time)) {
			$time = strtotime($time);
		}
		$dur = $now - $time;
		if($dur < 3600) {
			if($dur<50){
				$second = ceil($dur / 10)*10;
				if ($second<=0){
					$second = 10;
				}
				$time = sprintf(TIME_FORMAT_SECONDTE, $second);
			}else{
				$minutes = ceil($dur / 60);
				if ($minutes<=0){
					$minutes = 1;
				}
				$time = sprintf(TIME_FORMAT_MINITE, $minutes);
			}
		}elseif(date("Ymd", $now) == date("Ymd", $time)) {
			$hour = ceil($dur / 3600);
			if ($hour <= 0){
					$hour = 1;
			}
			$time = sprintf(TIME_FORMAT_HOUR, $hour);
		}else{
			$day = ceil($dur / 86400);
			if($day < 7){
				if ($day <= 0){
						$day = 1;
				}
				$time = sprintf(TIME_FORMAT_DAY, $day);
			} else {
				$time = TIME_FORMAT_WEEK;
			}
		}
		return $time;
	}
	
	public static function eventTimeFormat($start, $end) {
		$week = array(1=>"一",2=>"二",3=>"三",4=>"四",5=>"五",6=>"六",7=>"日");
		$sweek = date("N", $start);
		$eweek = date("N", $end);
		if(date("Ymd", $start) == date("Ymd", $end)) {//同一天
			$time = sprintf(TIME_FORMAT_EVENT_NOYEAR, date("n", $start), date("j", $start), $week[$sweek], date("H:i", $start) . " - " . date("H:i", $end));
		}else
			if(date("Y", $start) == date("Y", $end)) {//同一年不同天
			$stime = sprintf(TIME_FORMAT_EVENT_NOYEAR, date("n", $start), date("j", $start), $week[$sweek], date("H:i", $start));
			$etime = sprintf(TIME_FORMAT_EVENT_NOYEAR, date("n", $end), date("j", $end), $week[$eweek], date("H:i", $end));
			$time = $stime . " - " . $etime;
		}else {//不是同一年
			$stime = sprintf(TIME_FORMAT_EVENT_WITHYEAR, date("Y", $start), date("n", $start), date("j", $start), $week[$sweek], date("H:i", $start));
			$etime = sprintf(TIME_FORMAT_EVENT_WITHYEAR, date("Y", $end), date("n", $end), date("j", $end), $week[$eweek], date("H:i", $end));
			$time = $stime . " - " . $etime;
		}
		return $time;
	}
	
	
/*
 * 格式化为00：00：00
 *
 * 
 * */
	public static function  intervalFormat($time) {
		$time = floor($time);
		$hour = self::intervalHour($time);
		$minute  = self::intervalMinute($time - 3600*$hour);
		$second  = self::intervalSecond($time - 3600*$hour - 60*$minute);
		return sprintf("%02d:%02d:%02d",$hour,$minute,$second);
	}	
	public static function intervalHour($time){
	if ($time>=3600){
	   return floor($time/3600);        //多少小时
	}
	}
	public static function intervalMinute($time){
	if ($time>=60 and $time<3600){
	   return floor($time/60);        //多少分钟
	}
	}
	public static function intervalSecond($time){
	if ($time>0 and $time<60){
	   return $time;        //多少秒
	}
	}
	
}