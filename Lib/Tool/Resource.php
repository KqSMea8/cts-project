<?php
/**
 * 资源连接类, 资源地址定义在config/resource.php 中
*
* @package    Tool
* @copyright  copyright(2011) weibo.com all rights reserved
* @author     wangguan <wangguan@staff.sina.com.cn>
*/

class Tool_Resource {

	/**
	 * 连接Redis
	 * @param	string	$alias	Redis资源别名
	 * @return	Redis object
	 */
	public static function connectRedis($alias, $type = 'online'){
		$alias = strtolower($alias);
		$redis_config = Comm_Config::get('resource.redis.' . $type);
		
		if(isset($redis_config[$alias])) {
			$config = $redis_config[$alias];
		} else {
			return false;
		}

		$server = explode( ':', $config );
		if( empty($server) || !is_array($server) ) {
			return false;
		}
		$host = trim($server[0]);
		$port = $server[1];

		$redisObj = new Redis();
		$result = $redisObj->connect($host,$port);
		if ($result == false)
		{
			Tool_Log::info("redisq connect error:{$alias}--".$config);
			return false;
		}
		return $redisObj;
	}

}