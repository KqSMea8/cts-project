<?php
/**
 * 超时时间检测
 */
class Sso_Sdk_Tools_Timer{

	private static $_arr_timer = array();

	/**
	 * 启动一个计时器
	 * @param $tag
	 * @return null|int
	 */
	public static function start($tag) {
		if (count(self::$_arr_timer) > 1000) return null;   // 避免产生大量的计数器
		static $i = 1;
		$timer = $i++;
		self::$_arr_timer[$timer] = array('start' => microtime(1) * 1000, 'tag'=>$tag);
		return $timer;
	}

	/**
	 * 结束一个计时器
	 * @param $timer
	 * @param $threshold
	 * @return int
	 */
	public static function stop($timer, $threshold = 0) {
		list($tag, $time_use) = self::_stop($timer);
		if ($tag === null) return $time_use;
		if ($time_use > $threshold) {
			Sso_Sdk_Tools_Debugger::warn($time_use.':'. $threshold, "$tag time use");
			Sso_Sdk_Tools_Log::instance()->timelog($tag, $time_use, $threshold);
		}
		return $time_use;
	}

	/**
	 * 根据设置随机启动一个定时器，用于采样一些时间
	 * @param $tag
	 * @return null|int
	 */
	public static function rand_start($tag) {
		if (($arr = Sso_Sdk_Config::instance()->get("data.main.time.sample.$tag")) === null ) return null;
		if (!isset($arr['percent']) || !isset($arr['max'])) return null;
		if (mt_rand(1, $arr['max']) > $arr['percent']) return null;

		Sso_Sdk_Tools_Debugger::info($arr, "start sample $tag");
		return self::start($tag);
	}

	/**
	 * 结束一个随机采样的计时器
	 * @param $timer
	 * @param $threshold
	 * @return int
	 */
	public static function rand_stop($timer, $threshold = 0) {
		list($tag, $time_use) = self::_stop($timer);
		if ($tag === null) return $time_use;
		if ($time_use > $threshold) {
			Sso_Sdk_Tools_Debugger::info($time_use.':'. $threshold, "$tag time use");
			Sso_Sdk_Tools_Log::instance()->timelog($tag, $time_use, $threshold);
		}
		return $time_use;
	}

	private static function _stop($timer) {
		if ($timer === null || !isset(self::$_arr_timer[$timer])) return array(null, 0);
		$time_use = round(microtime(1) * 1000 - self::$_arr_timer[$timer]['start'], 2);
		$tag = self::$_arr_timer[$timer]['tag'];
		unset(self::$_arr_timer[$timer]);
		return array($tag, $time_use);
	}
}