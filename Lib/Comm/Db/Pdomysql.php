<?php 

/**
 * 使用PDO驱动的MySQL连接对象。
 * 
 * 该连接对象支持读写分离，迟连接，并采用参数化查询的方式来防止SQL注入。<br/>
 * 
 * 该连接对象本身除强制参数化查询方法以外，并未对PDO Mysql做过多封装，仍然可以使用PDO的配置项对该类实例进行更细致的配置。<br/>
 * 
 * 基本配置请见 Comm_Db 类的示例<br/>
 * 
 * 基本使用方法:<br/>
 * 
 * 以下配置将会产生一个PdoMysql的数据连接，并且写库使用 SINASRV_DB1_* 系列环境变量，而读库则会从读使用 SINASRV_DB1_*_R 系列环境变量的配置和 SINASRV_DB1_*_R2 系列环境变量的配置中随机挑选一个。
 * <code>
 * $mysql = new Comm_Db_PdoMysql();
 * $mysql->configure('user', 
 * 		array( // 数据库连接别名
 * 			'write' => array( // 配置别名。Comm_Db_PdoMysql 会依据别名本身中是否包含read和write来区别读写配置。
 * 				'host' => $_SERVER['SINASRV_DB1_HOST'], 
 * 				'port' => $_SERVER['SINASRV_DB1_PORT'], 
 * 				'name' => $_SERVER['SINASRV_DB1_NAME'], 
 * 				'user' => $_SERVER['SINASRV_DB1_USER'], 
 * 				'pass' => $_SERVER['SINASRV_DB1_PASS'],
 * 			),
 * 			'read'  => array(
 * 				'host' => $_SERVER['SINASRV_DB1_HOST_R'], 
 * 				'port' => $_SERVER['SINASRV_DB1_PORT_R'], 
 * 				'name' => $_SERVER['SINASRV_DB1_NAME_R'], 
 * 				'user' => $_SERVER['SINASRV_DB1_USER_R'], 
 * 				'pass' => $_SERVER['SINASRV_DB1_PASS_R'],
 * 			),
 * 			'read2'  => array(
 * 				'host' => $_SERVER['SINASRV_DB1_HOST_R2'], 
 * 				'port' => $_SERVER['SINASRV_DB1_PORT_R2'], 
 * 				'name' => $_SERVER['SINASRV_DB1_NAME_R2'], 
 * 				'user' => $_SERVER['SINASRV_DB1_USER_R2'], 
 * 				'pass' => $_SERVER['SINASRV_DB1_PASS_R2'],
 * 			),
 * 		),
 * );
 *
 * //select语句将会从read及read2中的随机一套配置中进行读取
 * $users = $mysql->fetch_all('select * from user where user_id=:id', array(':id' => 123));
 * //若user_id 123存在，则$users应为一个二维数组，其一维为数据行，第二维为数据列
 * echo count($users) === 1 ? 'user 123 exist' : 'user 123 not exist';
 * 
 * //delete语句将会在write配置中进行删除
 * $mysql->exec('delete from user where user_id=?', array(123));
 * 
 * //强制使用写库以避免主从同步的延迟问题
 * $mysql->set_write();
 * $users = $mysql->fetch_all('select * from user where user_id=?', array(123));
 * //此时$users 将是个空数组
 * echo count($users) > 0 ? 'user 123 still exists' : 'user 123 not exist anymore';
 * //如果删除成功，则此处应显示 user 123 not exist anymore
 * 
 * </code>
 * 
 * 当使用Comm_Db提供的自动配置和数据库连接别名时，可以略微简化一些调用方法：
 * 
 * <code>
 * Comm_Db::auto_configure_pool(); // 使用 db_pool 作为默认配置文件名
 * $users = Comm_Db::get('user')->fetch_all('select * from user where user_id=:id', array(':id' => 123));
 * </code>
 * 
 * 其他一些读写共用配置:<br/>
 * 
 * 以下配置文件将会产生一个名字叫做user的数据库连接，并且读写都使用同一套配置。
 * <code>
 * // db_pool 配置文件：
 * return array(
 * 	'PdoMysql' => array(
 * 		'user' => array(
 * 			'readwrite' => array(
 * 				'host' => $_SERVER['SINASRV_DB1_HOST'], 
 * 				'port' => $_SERVER['SINASRV_DB1_PORT'], 
 * 				'name' => $_SERVER['SINASRV_DB1_NAME'], 
 * 				'user' => $_SERVER['SINASRV_DB1_USER'], 
 * 				'pass' => $_SERVER['SINASRV_DB1_PASS'],
 * 			),
 * 		),
 * 	)
 * );
 * </code>
 * 
 * 更复杂的读写分离配置:<br/>
 * 
 * 以下配置将会产生一个名字为user的读写分离的数据库连接，并且随机使用SINASRV_DB2、SINASRV_DB1_*_R、SINASRV_DB1_*_R2中某一个配置作为读库配置，随机使用SINASRV_DB1、SINASRV_DB2中某一个配置作为写库配置。
 * <code>
 * // db_pool 配置文件：
 * return array(
 * 	'PdoMysql' => array(
 * 		'user' => array(
 * 			'write' => array(
 * 				'host' => $_SERVER['SINASRV_DB1_HOST'], 
 * 				'port' => $_SERVER['SINASRV_DB1_PORT'], 
 * 				'name' => $_SERVER['SINASRV_DB1_NAME'], 
 * 				'user' => $_SERVER['SINASRV_DB1_USER'], 
 * 				'pass' => $_SERVER['SINASRV_DB1_PASS'],
 * 			),
 * 			'read_with_write2' => array(
 * 				'host' => $_SERVER['SINASRV_DB2_HOST'], 
 * 				'port' => $_SERVER['SINASRV_DB2_PORT'], 
 * 				'name' => $_SERVER['SINASRV_DB2_NAME'], 
 * 				'user' => $_SERVER['SINASRV_DB2_USER'], 
 * 				'pass' => $_SERVER['SINASRV_DB2_PASS'],
 * 			),
 * 			'read'  => array(
 * 				'host' => $_SERVER['SINASRV_DB1_HOST_R'], 
 * 				'port' => $_SERVER['SINASRV_DB1_PORT_R'], 
 * 				'name' => $_SERVER['SINASRV_DB1_NAME_R'], 
 * 				'user' => $_SERVER['SINASRV_DB1_USER_R'], 
 * 				'pass' => $_SERVER['SINASRV_DB1_PASS_R'],
 * 			),
 * 			'read2'  => array(
 * 				'host' => $_SERVER['SINASRV_DB1_HOST_R2'], 
 * 				'port' => $_SERVER['SINASRV_DB1_PORT_R2'], 
 * 				'name' => $_SERVER['SINASRV_DB1_NAME_R2'], 
 * 				'user' => $_SERVER['SINASRV_DB1_USER_R2'], 
 * 				'pass' => $_SERVER['SINASRV_DB1_PASS_R2'],
 * 			),
 * 		),
 * 	)
 * );
 * </code>
 * 
 * 对PDO连接对象进行更细致的配置: <br/>
 * 
 * 更多PDO连接属性配置，请移步： http://cn.php.net/manual/en/pdo.setattribute.php <br/>
 * <code>
 * // db_pool 配置文件：
 * return array(
 * 	'PdoMysql' => array(
 * 		'user' => array(
 * 			'readwrite' => array(
 * 				'host' => $_SERVER['SINASRV_DB1_HOST'], 
 * 				'port' => $_SERVER['SINASRV_DB1_PORT'], 
 * 				'name' => $_SERVER['SINASRV_DB1_NAME'], 
 * 				'user' => $_SERVER['SINASRV_DB1_USER'], 
 * 				'pass' => $_SERVER['SINASRV_DB1_PASS'],
 * 				'attr' => array(
 * 					PDO::ATTR_CASE => PDO::CASE_LOWER, // 强制列名使用小写
 * 					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // 当PDO出错时直接抛出异常而不是输出PHP Warning
 * 				),
 * 			),
 * 		),
 * 	)
 * );
 * </code>
 * 
 * @see Comm_Db
 * @package Common
 * @subpackage Db
 * @author Rodin <luodan@staff.sina.com.cn>
 * @copyright (c) 2011, PHP Team, tech.intra.weibo.com
 */
class Comm_Db_PdoMysql implements Comm_Db_Interface{
	static private $mode_auto = 0;
	static private $mode_read = 1;
	static private $mode_write = 2;
	
	/**
	 * 读库配置项
	 *
	 * @var array
	 */
	protected $read_config = array();
	
	/**
	 * 写库配置项
	 *
	 * @var array
	 */
	protected $write_config = array();
	
	/**
	 * 读库PDO实例
	 *
	 * @var PDO
	 */
	protected $read_inst;
	
	/**
	 * 写库PDO实例
	 *
	 * @var PDO
	 */
	protected $write_inst;
	
	/**
	 * 最后一次使用的PDO实例
	 *
	 * @var PDO
	 */
	protected $last_inst;
	
	/**
	 * 当前读写模式
	 *
	 * @var int
	 */
	protected $mode = 0;
	
	/**
	 * 默认别名
	 *
	 * @var string
	 */
	protected $alias = 'Undefined';
	
	/**
	 * 配置项数组可用key值
	 *
	 * @var array
	 */
	static protected $config_keys = array('host', 'port', 'name', 'user', 'pass', '*attr');
	
	/**
	 * 实现DB的configure接口
	 *
	 * @param string $alias  实例别名
	 * @param array $config  配置项
	 */
	public function configure($alias, $config){
		$this->alias = $alias;
		$read_configs = $write_configs = array();
		foreach ($config as $k => $v){
			self::check_config_format($v);
			
			if(strpos($k, 'read') !== false){
				$read_configs[] = $v;
			}
			if(strpos($k, 'write') !== false){
				$write_configs[] = $v;
			}
		}
		
		$read_configs AND $this->read_config = $read_configs[array_rand($read_configs)];
		$write_configs AND $this->write_config = $write_configs[array_rand($write_configs)];
		if(!$this->read_config && !$this->write_config){
			throw new Comm_Exception_Program('Must define at least one db for "' . $this->alias . '"!');
		}
	}
	
	/**
	 * 强制使用读库
	 * 
	 * 该方法可以使用链式调用继续访问当前对象。
	 * 
	 * @return Comm_Db_PdoMysql
	 */
	public function set_read(){
		$this->mode = 1;
		return $this;
	}

	/**
	 * 强制使用写库
	 * 
	 * 该方法可以使用链式调用继续访问当前对象。
	 * 
	 * @return Comm_Db_PdoMysql
	 */
	public function set_write(){
		$this->mode = 2;
		return $this;
	}
	
	/**
	 * 根据sql语句自行判断
	 * 
	 * 该方法可以使用链式调用继续访问当前对象。
	 * 
	 * @return Comm_Db_PdoMysql
	 */
	public function set_auto(){
		$this->mode = 0;
		return $this;
	}
	
	/**
	 * 执行一个sql语句并返回影响行数。
	 * 
	 * 如果在insert或者replace语句后需要获取 last insert id 请使用last_insert_id()方法
	 * 
	 * @param string $sql	sql语句。不能为select语句
	 * @param array $data
	 * @throws Comm_Exception_Program
	 * @throws Comm_Db_PdoMysqlException
	 */
	public function exec($sql, array $data = NULL){
		$statement = $this->execute_sql($sql, $data);
		
		return $statement->rowCount();
	}
	
	/**
	 * 执行提供的select语句并返回结果集。
	 * 
	 * @param string $sql	sql语句。只能为select语句
	 * @param array $data
	 * @param bool $fetch_index  结果集是否使用下标数字方式
	 * @return array 
	 */
	public function fetch_all($sql, array $data = NULL, $fetch_index = false){
		$verb = self::extract_sql_verb($sql);
		if($verb !== 'select'){
			throw new Comm_Exception_Program('Can not fetch on a non-select sql');
		}
		
		$statement = $this->execute_sql($sql, $data);
		
		return $statement->fetchAll($fetch_index ? PDO::FETCH_NUM : PDO::FETCH_ASSOC);
	}
	
	/**
	 * PDO同名方法封装
	 * 
	 * @see PDO::prepare() 
	 * @param string $sql
	 * @return PDOStatement
	 */
	public function prepare($sql){
		$pdo = $this->get_inst($this->detect_sql_type($sql));
		$args = func_get_args();
		return call_user_func_array(array($pdo, 'prepare'), $args);
	}
	
	/**
	 * PDO同名方法封装
	 * 
	 * @param string $sql
	 * @return PDOStatement
	 */
	public function query($sql){
		$pdo = $this->get_inst($this->detect_sql_type($sql));
		$args = func_get_args();
		return call_user_func_array(array($pdo, 'query'), $args);
	}
	
	/**
	 * PDO::getAttribute() 方法的重命名封装版
	 * @param int $attribute
	 * @return mixed
	 */
	public function get_attribute($attribute){
		return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
	}
	
	/**
	 * PDO::setAttribute() 方法的重命名封装版
	 * 
	 * @param int $attribute
	 * @param mixed $value
	 */
	public function set_attribute($attribute, $value){
		$this->attributes[$attribute] = $value;
	}
	
	public function close(){
		$this->read_inst = NULL;
		$this->write_inst = NULL;
		$this->last_inst = NULL;
	}
	
	public function __call($func, $args){
		//Convert do_something() style to dosomething()
		$func = str_replace('_', '', strtolower($func));
		//Because of class method name is case insensitive in PHP, so, this simple 
		//    process is enough and fast.
		
		$mode = self::$mode_auto;
		if(in_array($func, array('lastinsertid', 'begintransaction', 'intransaction', 'commit', 'rollback'))){
			$mode = self::$mode_write;
		}
		return call_user_func_array(array($this->get_inst($mode), $func), $args);
	}
	
	/**
	 * 执行一个sql并返回PDOStatement对象和执行结果。
	 * 
	 * @param string $sql
	 * @param array $data
	 * @return mixed
	 */
	protected function execute_sql($sql, array $data = NULL){
		$statement = $this->prepare($sql);
		/* @var $statement PDOStatement */
		if($data){
			$result = $statement->execute($data);
		}else{
			$result = $statement->execute();
		}
		if(!$result){
			$error = $statement->errorInfo();
			if(is_array($error)){
				$error = implode(',', $error);
			}else{
				$error = strval($error);
			}
			throw new Comm_Db_PdoMysqlException($error);
		}
		
		return $statement;
	}
	
	/**
	 * 根据指定的类型获取pdo实例。
	 * 
	 * @param int $mode
	 * @return PDO
	 */
	protected function get_inst($mode){
		if($mode === self::$mode_auto){
			if(null === $this->last_inst){
				//default read, unless set write mode
				$this->last_inst = $this->get_inst(
					$this->mode === self::$mode_write
						? self::$mode_write 
						: self::$mode_read
				);
			}
			return $this->last_inst;
		}
		if($mode === self::$mode_read){
			if(null === $this->read_inst){
				if(!$this->read_config){
					return $this->get_inst(self::$mode_write);
				}
				$this->read_inst = $this->get_pdo($this->read_config);
			}
			$this->last_inst = $this->read_inst;
			return $this->read_inst;
		}
		if($mode === self::$mode_write){
			if(null === $this->write_inst){
				if(!$this->write_config){
					throw new Comm_Exception_Program('Writable db must be defined');
				}
				$this->write_inst = $this->get_pdo($this->write_config);
			}
			$this->last_inst = $this->write_inst;
			return $this->write_inst;
		}
	}
	
	protected function get_pdo($config){
		try{
			$inst = new PDO("mysql:dbname={$config['name']};host={$config['host']};port={$config['port']}", $config['user'], $config['pass'], isset($config['options']) ? $config['options'] : array());
		}catch (Exception $ex){
			throw new Comm_Db_PdoMysqlException("mysql:dbname={$config['name']};host={$config['host']};port={$config['port']};" . $ex->getMessage());
		}
		if(!empty($config['attr']) && is_array($config['attr'])){
			foreach ($config['attr'] as $k => $v){
				$inst->setAttribute($k, $v);
			}
		}
		
		return $inst;
	}
	
	/**
	 * 提取sql语句的动词
	 * 
	 * @param string $sql
	 * @return string 动词
	 */
	static protected function extract_sql_verb($sql){
		$sql_components = explode(' ', ltrim($sql), 2);
		$verb = strtolower($sql_components[0]);
		return $verb;
	}
	
	/**
	 * 检测sql所需的数据库类型
	 * @param string $sql
	 * @return ENUM
	 */
	static protected function detect_sql_type($sql){
		if(self::extract_sql_verb($sql) === 'select'){
			return self::$mode_auto;
		}
		return self::$mode_write;
	}
	
	/**
	 * 检查配置文件格式是否合格
	 * 
	 * @param array $config
	 * @throws Comm_Exception_Program
	 */
	protected function check_config_format(array $config){
		$valid_keys = array_fill_keys(self::$config_keys, 0);
		foreach ($config as $k => $v){
			//检查是否是必选或者可选参数。可选参数以*号开头
			if(!isset($valid_keys[$k]) && !isset($valid_keys["*$k"])){
				throw new Comm_Exception_Program('Unused PdoMysql "' . $this->alias . '" config "' . $k . '"');
			}
			unset($valid_keys[$k]);
		}
		
		if($valid_keys){
			$keys = array_keys($valid_keys);
			//忽略掉可选参数。可选参数以*号开头
			do{
				$key = array_pop($keys);
			}while ($key{0} === '*');
			if($key && $key{0} !== '*'){
				throw new Comm_Exception_Program('Missing PdoMysql "' . $this->alias . '" config value "' . $key . '"');
			}
		}
	}
} 