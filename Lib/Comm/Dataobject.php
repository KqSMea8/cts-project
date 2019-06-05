<?php 

/**
 * 业务模型封装
 * 
 * 提供常用的参数格式判断、属性别名(通过Hook方法实现)以及逻辑封装容器功能。
 * 
 * Tutorial:
 * <code>
 * class Do_Test_Example extends Comm_DataObject{
 * 	protected $props = array(
 * 		'id' => array(
 * 			'int', //checker type
 * 			'min,5;max;5000000', //rules
 * 			Comm_Argchecker::OPT_USE_DEFAULT, //是否必须。常量含义为非必须，若未提供，则使用默认值。
 * 			Comm_ArgChecker::RIGHT, //是否必须对。常量含义为必须正确，不会使用默认值。
 * 			'0', //默认值
 * 		),
 * 		'mid' => '', //忽略其验证过程。但由于存在其 set_mid 方法，所以会优先执行set_mid，而set_mid里逻辑表明mid只是id的别名，且使用public方法来设置id值，则id对应的检查被触发。
 * 		'createtime' => '', //忽略其验证过程。但由于其存在 set_mid 方法，且该方法中的逻辑直接设置了内部属性，所以，createtime的检查和判断只在 set_mid 方法中完成
 * 		'author' => 'Do_Test_User', //应传入一个数组以自动创建一个Do_Test_User对象
 * 	);
 * 	
 * 	public function set_createtime($value){
 * 		$this->set_data('createtime', is_numeric($value) ? $value : strptime($value, '%Y-%m-%d %H:%M:%S'));
 * 	}
 * 	
 * 	public function get_createtime($use_formated = null){
 * 		return $use_formated ? $this->get_data('createtime') : date('Y-m-d H:i:s', $this->get_data('createtime'));
 * 	}
 * 	
 * 	public function set_mid($value){
 * 		$this->id = $value;
 * 		//$this['id'] = $value;
 * 	}
 * 	
 * 	public function get_mid(){
 * 		return $this->id;
 * 		//return $this['id'];
 * 	}
 * }
 * </code>
 * @author Rodin <luodan@staff.sina.com.cn>
 *
 */
abstract class Comm_DataObject extends ArrayObject{
	/**
	 * 输入模式。用于在数据从创建到写入存储的过程中使用。在该模式下，会调用规则检查，子对象会递归创建，同时进行数据检查。
	 * @staticvar
	 * @final
	 */
	const MODE_INPUT = 'input';
	
	/**
	 * 输出模式。用于在数据从存储读出到过程处理和展示的过程中使用。在该模式下，创建和写入时信任数据，不会调用规则检查，子对象仍然会递归创建，同时信任数据。
	 * @staticvar
	 * @final
	 */
	const MODE_OUTPUT = 'output';
	
	protected $props = array();
	protected $mode;

	public function __construct($init_data = NULL, $mode = Comm_DataObject::MODE_INPUT){
		parent::setFlags(ArrayObject::ARRAY_AS_PROPS);
		$this->set_dataobject_mode($mode);
		if(!is_null($init_data)){
			$this->init_data($init_data);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayObject::offsetSet()
	 */
	public function offsetSet($prop_name, $value){
		if (!isset($this->props[$prop_name])) {
			if($this->mode === self::MODE_INPUT){//用户输入模式需要验证do无法写入不期望的数据
				throw new Comm_Dataobject_Exception('undefined property name:' . $prop_name);
			}else{//从数据库输出模式如果验证失败则不做任何处理，用以兼容api闲着么事加字段……
				return;
			}
		}
		
		if(method_exists($this, 'set_' . $prop_name)){
			return $this->{'set_' . $prop_name}($value);
		}elseif($this->mode === self::MODE_INPUT){
			return parent::offsetSet($prop_name, $this->apply_rule_on_property($prop_name, $value));
		}else{
		    return parent::offsetSet($prop_name, $value);
		}
	}
	
	/**
	 * 覆盖父类方法，提供类似数组方式的访问。
	 * 
	 * <ul>
	 * <li>对于未定义检查规则的项，认为是可设置项，在未被初始化的情况下返回Null。</li>
	 * <li>对于定义了get_*系列方法的项，则会直接返回get_*方法的返回值。</li>
	 * <li>对于定义了检查规则的项，认为是必设置项，在未被初始化的情况下抛出异常。</li>
	 * </ul>
	 * 
	 * @see ArrayObject::offsetGet()
	 */
	public function offsetGet($prop_name){
	    if (!isset($this->props[$prop_name])) {
	    	if($this->mode === self::MODE_INPUT){
	        	throw new Comm_Dataobject_Exception('undefined property name:' . $prop_name);
	    	}else{
	    		return;
	    	}
	    }
//		$this->assert_prop_available($prop_name);
		
		if(method_exists($this, 'get_' . $prop_name)){
			return $this->{'get_' . $prop_name}();
        } else {
            if ($this->props[$prop_name] && !parent::offsetExists($prop_name)) {
            	if($this->mode === Comm_DataObject::MODE_OUTPUT){
            		return NULL;
            	}else{
                	throw new Comm_Dataobject_Exception('property not set:' . $prop_name);
            	}
            }
            return parent::offsetExists($prop_name) ? parent::offsetGet($prop_name) : NULL;
		}
	}
	
	/**
	 * 获取数据对象模式
	 * 
	 * @return enum Comm_DataObject::MODE_* 系列常量。
	 */
	public function get_dataobject_mode(){
		return $this->mode;
	}
	
	/**
	 * 设置数据对象模式
	 * 
	 * @param enum $mode Comm_DataObject::MODE_* 系列常量。
	 * @throws Comm_Dataobject_Exception
	 */
	public function set_dataobject_mode($mode){
		if($mode !== Comm_DataObject::MODE_INPUT && $mode !== Comm_DataObject::MODE_OUTPUT){
			throw new Comm_Dataobject_Exception('mode incorrect');
		}
		$this->mode = $mode;
	}
	
	/**
	 * 返回当前对象的数组形式
	 * 
	 * @param bool $recursive 是否对子对象递归调用to_array。可选，默认为false。
	 * @return array
	 */
	public function to_array($recursive = false){
		if(!$recursive){
			return $this->getArrayCopy();
		}
		
		$array = $this->getArrayCopy();
		foreach ($array as $k => $v){
			if(is_object($v) && method_exists($v, 'to_array')){
				$array[$k] = $v->to_array($recursive);
			}
		}
		return $array;
	}
	
	public function __isset($prop){
		return $this->offsetExists($prop);
	}
	
	public function __unset($prop){
		return $this->offsetUnset($prop);
	}
	
	public function __get($prop){
		return $this->offsetGet($prop);
	}
	
	public function __set($prop, $value){
		return $this->offsetSet($prop, $value);
	}
	
	/**
	 * 删除所有的引用，释放对象
	 */
	public function __destruct(){
		parent::exchangeArray(array());
//		if($this->count()){
//			foreach ($this as $prop => $v){
//				$v = null;
//				parent::offsetSet($prop, null);
//			}
//			$this->exchangeArray(array());
//		}
	}	
	
	protected function init_data($data){
		if(is_object($data) || is_array($data)){
			foreach ($data as $key => $value){
				$this->offsetSet($key, $value);
			}
		}else{
			throw new Comm_Dataobject_Exception('init data should be an object or array');
		}
	}
	
	protected function assert_prop_available($name){
		if(!isset($this->props[$name])){
			throw new Comm_Dataobject_Exception('undefined property name:' . $name);
		}
	}
	
	/**
	 * 根据$this->props中定义的规则，进行规则检查。
	 * 
	 * 可以在 set_* 系列自定义函数中使用以实现默认规则的处理。
	 * 
	 * @see offsetSet()
	 * @param mixed $property
	 * @param mixed $value
	 * @throws Comm_Dataobject_Exception
	 */
	protected function apply_rule_on_property($property, $value){
		$validated_value = $value;
		if(is_array($this->props[$property])){
			if($this->mode === Comm_DataObject::MODE_INPUT){
				$arg_args = $this->props[$property];
				$arg_base_type = $arg_args[0];
				$arg_args[0] = $value; 
				//如果不能通过规则校验，Argchecker会抛出异常。
				$validated_value = call_user_func_array(array('Comm_Argchecker', $arg_base_type), $arg_args);
			}
		}elseif($this->props[$property]){
			$class = $this->props[$property];
			if(!class_exists($class)){
				throw new Comm_Dataobject_Exception("data class of $property not exist: \{$class\}");
			}
			if(!is_object($value) || get_class($value) !== $class){
				$validated_value = new $class($value, $this->mode);
			}
		}
		return $validated_value;
	}
	
	/**
	 * 调用 ArrayObject::offsetSet() 来完成数据存储。此方法应该在set_*系列自定义方法中被使用用来替代 parent::offsetSet 防止产生循环引用。
	 * 
	 * @see ArrayObject::offsetSet()
	 * @final
	 * @access protected
	 * @param mixed $key
	 * @param mixed $value
	 */
	final protected function _set_data($key, $value){
		parent::offsetSet($key, $value);
	}
	
	/**
	 * 调用 ArrayObject::offsetGet() 来完成数据获取。此方法应该在 get_* 系列自定义方法中被使用以替代 parent::offsetGet 防止产生循环引用。
	 * 
	 * @see ArrayObject::offsetGet()
	 * @final
	 * @access protected
	 * @param mixed $key
	 * @return mixed
	 */
	final protected function _get_data($key){
		return parent::offsetGet($key);
	}
}