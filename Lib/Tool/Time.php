<?php
/**
 * 时间工具类
 * @author chusheng@staff.sian.com.cn
 * @package tool
 * @version 2012-12-5
 */
define("TIME_FORMAT_SECONDTE", "%s秒前");
define('TIME_FORMAT_JUST', '刚刚');
define("TIME_FORMAT_MINITE", "%s分钟前");
define('TIME_FORMAT_HOUR', '%s小时前');
define('TIME_FORMAT_DAY', '%s天前');
define('TIME_FORMAT_WEEK', '%s周前');
define("TIME_FORMAT_TODAY", "今天 %s");
define("TIME_FORMAT_YESTODAY", "昨天 %s");
define("TIME_FORMAT_HISTORY", "%s-%s-%s");
define("TIME_FORMAT_HISTORY_VISITOR", "%s月%s日");
define('TIME_FORMAT_CAPTION_TODAY', '今天');
define('TIME_FORMAT_CAPTION_YESTODAY', '昨天');
define('TIME_FORMAT_CAPTION_YEAR', '年');
define('TIME_FORMAT_CAPTION_MONTH', '月');
define('TIME_FORMAT_CAPTION_DAY', '日');
define('TIME_FORMAT_CAPTION_HOUR', '点');
define('TIME_FORMAT_CAPTION_MINITE', '分');
define('TIME_FORMAT_CAPTION_SECOND', '秒');
define('TIME_FORMAT_EVENT_NOYEAR', "%s月%s日 周%s %s");
define('TIME_FORMAT_EVENT_WITHYEAR', "%s年%s月%s日 周%s %s");

class Tool_Time {
	
	public static $week_arr = array(0 => '周日', 1 => '周一', 2 => '周二', 3 => '周三', 4 => '周四', 5 => '周五', 6 => '周六', 7 => '周日');
	public static $week_num_arr = array(0 => '日', 1 => '一', 2 => '二', 3 => '三', 4 => '四', 5 => '五', 6 => '六', 7 => '日');
	public static $month_cn = array(
			"1" => "一",
			"2" => "二",
			"3" => "三",
			"4" => "四",
			"5" => "五",
			"6" => "六",
			"7" => "七",
			"8" => "八",
			"9" => "九",
			"10" => "十",
			"11" => "十一",
			"12" => "十二",
	);
	
	/**
	 * 静态调用模式，禁止实例化该类
	*/
	final public function __construct() {
		throw new Swift_Exception_Program('Can not new this class.');
	}
	
	/**
	 * 把unix时间戳转换成显示的格式 2月1日
	 * @param $timestamp unix时间戳
	 * @param bool $year 默认false 需要用到的时候传true 进来就行
	 */
	static public function getformattime($timestamp, $year=false) {
		$time = $year ? date('Y年n月d日 H:i', $timestamp) : date('n月d日 H:i', $timestamp);
		return $time;
	}
	
	/**
	 * 把unix时间戳转换成显示的格式 6月12日周六15：00
	 * @param $timestamp unix时间戳
	 * @param bool $year 默认false 需要用到的时候传true 进来就行
	 */
	static public function getalltime($timestamp, $year=false) {
		if (!$year) {
			$time = date('Y年n月d日', $timestamp);
			$time1 = self::$week_arr[date('N', $timestamp)];
			$time2 = date('H:i:s', $timestamp);
		} else {
			$time = date('n月d日', $timestamp);
			$time1 = self::$week_arr[date('N', $timestamp)];
			$time2 = date('H:i', $timestamp);
		}
		return $time . ' ' . $time1 . ' ' . $time2;
	}
	
	/**
	 * 把unix时间戳转换成显示的格式 2月1日不带小时的
	 * @param $timestamp unix时间戳
	 */
	static public function getformattimesim($timestamp) {
		$time = date('n月d日', $timestamp);
		return $time;
	}
	
	/**
	 * 把unix时间戳转换成显示的格式 如果跨年的就显示年，如果不跨年就不显示年
	 * @param $timestamp unix时间戳
	 */
	static public function getyeartime($timestamp, $his='') {
		$nowyear = getdate();
		$yearunix = strtotime($nowyear['year'] . '-12-31 23:59:59');
		if (empty($his))
			return $timestamp > $yearunix ? date('Y年n月d日', $timestamp) : date('n月d日', $timestamp);
		else
			return $timestamp > $yearunix ? date('Y年n月d日 H:i:s', $timestamp) : date('n月d日  H:i:s', $timestamp);
	}
	
	/**
	 * 把unix时间戳转换成周几暂时 只有event（活动详情页面调用 ）
	 * @param $timestamp unix时间戳
	 */
	static public function getweektime($timestamp) {
		$week_time = date('N', $timestamp);
		return self::$week_arr[$week_time];
	}
	
	/**
	 * 格式化显示一条时间
	 * @param	int		$time	时间戳
	 * @return	string
	 */
	public static function format($time) {
		$now = strtotime(NOW);
		if (strpos($time, '-') !== false) {
			$time = strtotime($time);
		}
		if (($dur = $now - $time) < 3600) {
			if ($dur < 50) {
				$second = ceil($dur / 10) * 10;
				if ($second <= 0) {
					$second = 10;
				}
				$time = sprintf(TIME_FORMAT_SECONDTE, $second);
			} else {
				$minutes = ceil($dur / 60);
				if ($minutes <= 0) {
					$minutes = 1;
				}
				$time = sprintf(TIME_FORMAT_MINITE, $minutes);
			}
		} else
			if (date("Ymd", $now) == date("Ymd", $time)) {
			$time = sprintf(TIME_FORMAT_TODAY, date("H:i", $time));
		} else {
			if (date("Y") == date("Y", $time)) {
				$time = sprintf(TIME_FORMAT_HISTORY_VISITOR, date("n", $time), date("j", $time)) . " " . date("H:i", $time);
			} else {
				$time = sprintf(TIME_FORMAT_HISTORY, date("Y", $time), date("n", $time), date("j", $time)) . " " . date("H:i", $time);
			}
		}
		return $time;
	}
	
	/**
	 * 格式化显示一条时间
	 * @param	int		$time	时间戳
	 * @return	string
	 */
	public static function format_group($time) {
		$now = NOW;
		if (strpos($time, '-') !== false) {
			$time = strtotime($time);
		}
		if (($dur = $now - $time) < 3600) {
			if ($dur < 50) {
				$second = ceil($dur / 10) * 10;
				if ($second <= 0) {
					$second = 10;
				}
				$time = sprintf(TIME_FORMAT_SECONDTE, $second);
			} else {
				$minutes = ceil($dur / 60);
				if ($minutes <= 0) {
					$minutes = 1;
				}
				$time = sprintf(TIME_FORMAT_MINITE, $minutes);
			}
		} else
			if (date("Ymd", $now) == date("Ymd", $time)) {
			$time = sprintf(TIME_FORMAT_TODAY, date("H:i", $time));
		} else {
			$time = sprintf('%s-%s %s:%s', date("m", $time), date("d", $time), date("H", $time), date("i", $time));
		}
		return $time;
	}

    /**
     * get_waiting_time 
     * 现在离某时间差多久
     * @param mixed $timestamp 
     * @static
     * @return void
     */
    public static function get_waiting_time($timestamp){
        $now = strtotime('now');
        $diff = $timestamp - $now;

        if ($diff < 0){//负数则返回
            return false;
        }

        $hour = floor($diff / 3600);
        $minute = floor(($diff - ($hour * 3600)) / 60);
        $second = $diff - ($hour * 3600) - ($minute * 60);
        if ($hour <= 0){
            return sprintf('%d分%d秒', $minute, $second);
        }else if ($hour <= 0 && $minute <= 0){
            return sprintf('%d秒', $second);
        }
        return sprintf('%d时%d分%d秒', $hour, $minute, $second);
    }

    /**
     * get_waiting_time 
     * 现在离某时间差多久（有天）
     * @param mixed $timestamp 
     * @static
     * @return void
     */
    public static function get_waiting_time_day($timestamp){
        $now = strtotime('now');
        $diff = $timestamp - $now;

        if ($diff < 0){//负数则返回
            return false;
        }

        $day = floor($diff / 86400);
        $hour = floor(($diff - ($day * 86400)) / 3600);
        $minute = floor(($diff - ($day * 86400) - ($hour * 3600)) / 60);
        $second = $diff - ($day * 86400) - ($hour * 3600) - ($minute * 60);
        if ($day <= 0){
            if ($hour <= 0){
                return sprintf('0天0时%d分%d秒', $minute, $second);
            }else if ($hour <= 0 && $minute <= 0){
                return sprintf('0天0时0分%d秒', $second);
            }
            return sprintf('0天%d时%d分%d秒', $hour, $minute, $second);
        }
        return sprintf('%d天%d时%d分%d秒', $day, $hour, $minute, $second);
    }
	
	/**
	 * 格式化显示一条时间
	 * @param	int		$time	时间戳
	 * @return	string
	 */
	public static function format_events_list($stime, $etime) {
		$stime = sprintf(TIME_FORMAT_EVENT_NOYEAR, date("m", $stime), date("d", $stime), self::$week_num_arr[date("N", $stime)], date("H:i", $stime));
		$etime = sprintf(TIME_FORMAT_EVENT_NOYEAR, date("m", $etime), date("d", $etime), self::$week_num_arr[date("N", $etime)], date("H:i", $etime));
		return $stime . " - " . $etime;
	}
	
	/**
	 * 格式化时间
	 *
	 * 1.完整格式如:				2011年5月1日 09:05
	 * 2.本年时间则不显示年份,如: 5月1日 09:05
	 *
	 * @author xiaowu1@
	 * @param int $time				时间戳
	 * @return string
	 */
	static public function format_time($time) {
		if (date('Y', $time) != date('Y')) {//非本年
			$format = 'Y年n月j日 H:i';
		} else {//本年不显示年份了
			$format = 'n月j日 H:i';
		}
	
		return date($format, $time);
	}
	
	/**
	 * 格式化两个时间
	 * 2011年3月2日 周六 23:02 - 2012年3月2日 周六 09:02
	 * 2011年3月2日 周六 10:02 - 18:02
	 * 3月2日 周六 10:02 - 18:02
	 *
	 * @xiaowu
	 * @param int $stime
	 * @param int $etime
	 * @param bool $is_week 是否显示星期
	 * @return string
	 */
	static public function two_time($stime, $etime, $is_week=false) {
		$n_y = date('Y');
		$s_info = getdate($stime);
		$e_info = getdate($etime);
	
		//开始时间
		$w = $is_week ? ' ' . self::getweektime($stime) : '';
		if ($s_info['year'] != $n_y) { //非本年
			$format = "Y年n月j日{$w} H:i";
		} else {//本年不显示年份了
			$format = "n月j日{$w} H:i";
		}
		$str = date($format, $stime);
	
		//结束时间
		if ($e_info['year'] == $s_info['year'] && $e_info['yday'] == $s_info['yday']) {//同年月日
			$format = 'H:i';
		} else {
			$w = $is_week ? ' ' . self::getweektime($etime) : '';
			if ($e_info['year'] == $s_info['year']) {//同年
				$format = "n月j日{$w} H:i";
			} else {
				if ($e_info['year'] != $n_y) { //非本年
					$format = "Y年n月j日{$w} H:i";
				} else {//本年不显示年份了
					$format = "n月j日{$w} H:i";
				}
			}
		}
		$str .= ' - ' . date($format, $etime);
	
		return $str;
	}
	
	/**
	 * 给定一个日期，获取它所在周的起止日期范围（周一为每周的第一天）
	 * @param string $date 日期：2011-04-05
	 * @return array 日期范围：
	 */
	static public function get_week_range($date) {
	
		///所给日期是周几
		$week = date("w", strtotime($date));
		$tmp = explode("-", $date);
	
		///上个周日的日期
		$sat = mktime(0, 0, 0, $tmp[1], $tmp[2] - $week, intval($tmp[0]));
		return array("start" => date("Y-m-d", $sat + 86400), "end" => date("Y-m-d", $sat + 7 * 86400));
	}
	
	/**
	 * 把秒数转化成方便阅读的时间段，如：3天4小时8分9秒，3小时0分0秒
	 * @param type $n  秒数
	 * @return string
	 */
	static public function length_time( $n ){
		$str = '';
		if( $n <= 0 ){
			return $str;
		}
	
		//天数
		$d = intval( $n / 24 / 3600 );
		$d > 0 && $str .= $d.'天';
		$n = $n % (24*3600);
	
		//小时
		$h = intval( $n / 3600 );
		($h || $str) && $str .= $h.'小时';
		$n = $n % 3600;
	
		//分
		$m = intval( $n / 60 );
		($m || $str) && $str .= $m.'分';
		$n = $n % 60;
	
		//秒
		$str .= $n.'秒';
	
		return $str;
	}
/**
 * 验证活动时间
 * @param int $start_time
 * @param int $end_time
 * @return number
 */
	public static function is_valid_activity_time($start_time, $end_time){
		if($start_time <= 0 || $end_time <= 0){
			Tool_Log::warning(Comm_Util::i18n('ajax.activity.invalid_time'));
			throw new Comm_Exception_Program(Comm_Util::i18n('ajax.activity.invalid_time'), Comm_Config::get('riacode.param'));
		}
		if($start_time >= $end_time){
			Tool_Log::warning(Comm_Util::i18n('ajax.activity.start_later_than_end'));
			throw new Comm_Exception_Program(Comm_Util::i18n('ajax.activity.start_later_than_end'), Comm_Config::get('riacode.param'));
		}
		if($end_time <= time()){
			Tool_Log::warning(Comm_Util::i18n('ajax.activity.now_later_than_end'));
			throw new Comm_Exception_Program(Comm_Util::i18n('ajax.activity.now_later_than_end'), Comm_Config::get('riacode.param'));
		}
		return true;
	}
	
/**
 * 转化成时间戳
 * @param int $time 日期
 * @param int $hour 小时数
 * @param int $min	分钟数
 * @return number	返回时间戳
 */
	public static function get_timestamp($time, $hour, $min){
		return strtotime($time . ' ' . $hour . ':' . $min . ':00');
	}

/**
 * 把时间秒数转换成（X天X小时X分钟）格式
 * @param int $n 秒数
 * @return string 转化后的字符串
 */
	
	public static function format_timediff($n){
		if($n<0)
		{
			return '';
		}	
		$str = '';
		//天数
		$day = intval($n/24/3600);
		$day > 0 && $str .= $day.'天';
		$n = $n % (24*3600);
		//小时数
		$hour = intval( $n/3600 );
		($hour || $str) && $str .= $hour.'小时';
		$n = $n % 3600;	
		//分钟
		$mins = intval( $n/60 );
		($mins || $str) && $str .= $mins.'分钟';
		
		return empty($str)? '1分钟': $str;
	}
	/**
	 * 获取一天剩余的秒数 added by liuyu6
	 * @return int
	 */
	public static function get_rest_seconds_of_one_day() {
	    $now = date('H:i:s');
	    $arr = explode(":",$now);
	
	    $h = $arr[0] * 3600;
	    $i = $arr[1] * 60;
	    $past = $h + $i + $arr[2];
	
	    $total = 24 * 60 * 60;
	    $rest = $total - $past;
	
	    return $rest;
	}

    /**
     *接收两个时间，将时间之间的差值转换成支付接口可以识别的字串
     *e.g 1d 5m 6h
     */
	public static function convert_time_to_pay($start = '', $end = '', $unixtime = '') {
    	if ($start && $end) {
    		$start_time = strtotime($start) ;
	        $end_time = strtotime($end) ;
	        $diff = $end_time - $start_time ;
	        if ($diff < 0) {
	        	return false;
	        }
    	} elseif ($unixtime) {
    		$diff = $unixtime ;
    	} else {
    		return false ;
    	}
    	
        $diff_for_m = $diff/60 ;
        $result = '' ;
        if ($diff_for_m < 60){
            $result = ceil($diff_for_m) . 'm' ;
        } else {
            $diff_for_h = $diff_for_m/60 ;
            if($diff_for_h > 72) {
                $diff_for_h = 72 ;
            }
            if($diff_for_h % 24 == 0) {
                $result = $diff_for_h/24 . 'd' ;
            } else {
                $result = ceil($diff_for_h) . 'h';
            }
        }
        return $result ;
    }

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
}
