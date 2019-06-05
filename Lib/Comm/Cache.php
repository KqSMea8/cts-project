<?php 

class Comm_Cache{
	static protected $pools = array();
	static public $key_config_name = 'cache_key';
	static public $pool_config_name = 'cache_pool';
	static public $configs = array();
	
	/**
	 * 根据名字获取一个缓存实例
	 * 
	 * @param string $name
	 * @return Comm_Cache_Interface
	 * @throws Comm_Exception_Program
	 */
	static public function get($name){
		return self::pool($name);
	}

	/**
	 * 根据名字获取一个缓存实例
	 * 
	 * @param string $name
	 * @return Comm_Cache_Interface
	 * @throws Comm_Exception_Program
	 */
	
	static public function pool($name){
	    return self::connect($name, 'Memcached');
	}
	
	/**
	 * 创建一个缓存实例
	 * 
	 * @param string $name
	 * @param string $backend
	 */
	static public function connect($name, $backend='Memcached'){
		if(!isset(self::$configs[$backend][$name])){
			throw new Comm_Exception_Program("pool $name not defined");
		}
		$config = self::$configs[$backend][$name];
		$class = 'Comm_Cache_' . $backend;
        if (!class_exists($class) || !in_array('Comm_Cache_Interface', class_implements($class))) {
            throw new Comm_Exception_Program('Cache type must be a valid backend type and implements Comm_Cache_Interface');
        }
        $cache = new $class;
        $cache->configure($config);
        return $cache;
	}
	
	/**
	 * 将缓存绑定到一个名字。当缓存名字已经占用的时候，会抛出一个异常。
	 * 
	 * @param string $name
	 * @param Comm_Cache_Interface $cache
	 * @throws Comm_Exception_Program
	 */
	static public function bind($name, Comm_Cache_Interface $cache){
		if(isset(self::$pools[$name])){
			throw new Comm_Exception_Program("Cache $name already defined");
		}
		
		self::$pools[$name] = $cache;
	}
	
	/**
	 * 将指定的缓存名字与其实例解绑。并返回被解绑的实例。
	 * @param string $name
	 * @return Comm_Cache_Interface
	 */
	static public function unbind($name){
		if(isset(self::$pools[$name])){
			$instance = self::$pools[$name];
			unset(self::$pools[$name]);
		}else{
			$instance = null;
		}
		return $instance;
	}
	
	/**
	 * 清除所有缓存名字与其实例的绑定。并返回被解绑的实例数组。
	 * @return array of Comm_Cache_Interface
	 */
	static public function unbind_all(){
		$pools = self::$pools;
		self::$pools = array();
		return $pools;
	}
	
	/**
	 * 根据key_config获取指定的$prefix与cache id的对应关系
	 * 
	 * 通常用 string => int 来减小key的总长度……
	 * 
	 * @param string	$prefix	前缀名
	 * @param mixed 	$extra	[可选]额外数据。支持多个参数。后续参数会与找到的prefix一起用下划线连接成一个完整的cache key
	 * @param mixed		...		[可选]
	 * @return string	完整的key名字
	 */
	static public function key($prefix, $extra = NULL){
		$id = Comm_Config::get(self::$key_config_name . '.' . $prefix);
		$args = func_get_args();
		$args[0] = $id;
		return implode('_', $args);
	}
	
	static public function auto_configure_pool($config_name = ''){
		$config = Comm_Config::get($config_name ? $config_name : self::$pool_config_name);
		
		foreach ($config as $type => $backend_confs){
			$class = 'Comm_Cache_' . $type;
			if(class_exists($class) && in_array('Comm_Cache_Interface', class_implements($class))){
			}else{
				$class = $type;
				if(class_exists($class) && in_array('Comm_Cache_Interface', class_implements($class))){
				}else{
					throw new Comm_Exception_Program('Cache type must be a valid backend type and implements Comm_Cache_Interface'); 
				}
			}
			foreach ($backend_confs as $name => $config){
			    self::$configs[$type][$name] = $config;
			}
		}
	}
}