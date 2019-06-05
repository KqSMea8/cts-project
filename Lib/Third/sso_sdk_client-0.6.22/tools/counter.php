<?php
/**
 * 计数器
 * 慎用计数器，计数器的访问也是有开销的
 */

/**
 * Class Sso_Sdk_Tools_Counter
 */
class Sso_Sdk_Tools_Counter {
	//常量定义不同类型的计数分类
	const TYPE_HTTP_VALIDATE_ERROR      = 'http_validate_error';
	const TYPE_HTTP_DESTROY_ERROR       = 'http_destroy_error';
	const TYPE_MEMCACHE_ERROR           = 'memcache_error';
	const TYPE_HTTP_SUS_VALIDATE_ERROR  = 'http_sus_validate_error';
	const TYPE_DNS_LOOKUP_ERROR         = 'dns_lookup_error';
	const TYPE_DNS_LOOKUP_TIMEOUT       = 'dns_lookup_timeout';
	const TYPE_HTTP_LOCK_QUERY_ERROR	= 'http_lock_query_error';
	const TYPE_HTTP_V3_VALIDATE_ERROR   = 'http_v3_validate_error';

	/**
	 * 针对指定计数器增加一个计数
	 * @param $type string
	 * @param string $key
	 * @return int
	 */
	public static function incr($type, $key = '') {
		//检查使用何种类型的计数器
		$arr = Sso_Sdk_Config::instance()->get("data.counter.$type");
		$period     = $arr['period'];
		$max        = $arr['max'];
		$duration   = $arr['duration'];

		try{
			$counter = Sso_Sdk_Config::get_counter();
		} catch (Exception $e){
			Sso_Sdk_Tools_Log::instance()->warn('counter', array_merge($arr, array('error'=>$e->getMessage(), 'errno'=>$e->getCode())));
			return 0;
		}
		if (!$key) {
			$key = $type;
		} else {
			$key = $type. '.'. $key;
		}
		//开始计数
		try{
			$count = $counter->incr($key, $period);
		} catch (Exception $e){
			Sso_Sdk_Tools_Log::instance()->notice('counter', array_merge($arr, array('error'=>$e->getMessage(), 'errno'=>$e->getCode())));
			return 0;
		}
		Sso_Sdk_Tools_Debugger::info(array('file' => $counter->get_storage_uri(), 'info' => "$count/$max/$period/$duration", 'type' => $type, 'key' => $key), 'counter');
		if ($count >= $max) {
			try{
				$counter->set($key, $count, $duration);
			} catch (Exception $e){
				Sso_Sdk_Tools_Log::instance()->notice('counter', array_merge($arr, array('error'=>$e->getMessage(), 'errno'=>$e->getCode())));
			}
			//访问计数达到最大值时，发送一条日志
			Sso_Sdk_Tools_Log::instance()->warn('counter', array_merge($arr, array('type'=>$type, 'key'=>$key)));
		}
		return $count;
	}

	/**
	 * 检查是否达到计数最大值
	 * @param $type string
	 * @param string $key
	 * @return bool
	 */
	public static function is_ok($type, $key = '') {
		try{
			$counter = Sso_Sdk_Config::get_counter();
		} catch (Exception $e){
			Sso_Sdk_Tools_Log::instance()->warn('counter', array('error'=>$e->getMessage(), 'errno'=>$e->getCode()));
			return 0;
		}
		if (!$key) {
			$key = $type;
		} else {
			$key = $type. '.'. $key;
		}
		try{
			$count = $counter->get($key);
		} catch (Exception $e){
			Sso_Sdk_Tools_Log::instance()->notice('counter', array('type'=>$type, 'error'=>$e->getMessage(), 'errno'=>$e->getCode()));
			return true;
		}
		$arr = Sso_Sdk_Config::instance()->get("data.counter.$type");
		$period     = $arr['period'];
		$max        = $arr['max'];
		$duration   = $arr['duration'];

		$result = $count < $max;
		if (!$result) {
			Sso_Sdk_Tools_Debugger::warn("$count/$max/$period/$duration", 'counter: '.$type. ':'. $key);
		}
		return $result;
	}
}