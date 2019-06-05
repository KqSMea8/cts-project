<?php 

/**
 * DataObject的集合
 * 
 * 为DataObject提供集合功能。方便绑定集合相关的操作。
 * 
 * Tutorial:
 * <code>
 * class Ds_Example extends Comm_DataObject_Set{
 * 	protected $child_class = 'Do_Test_Example';
 * 	
 * 	//implements the abstract method
 * 	protected function get_object_id($value) {
 * 		return isset($value['id']) ? $value['id'] : null;
 * 		//use return null to disable the object storage feature.
 * 	}
 * 	
 * 	
 * 	public function sort_by_createtime(){
 * 		return $this->uasort(array('Ds_Example', 'compare_createtime'));
 * 	}
 * 	
 * 	static protected function compare_createtime($a, $b){
 * 		return $a->createtime > $b->createtime ? 1 : ($a->createtime < $b->createtime ? -1 : 0); 
 * 	}
 * 	
 * 	public function filter_not_blocked($blocked){
 * 		$data = new Ds_Example();
 * 		foreach ($this as $v){
 * 			if(in_array($v->id, $blocked)){
 * 				continue;
 * 			}
 * 			$data->append($v);
 * 		} 
 * 		return $data;
 * 	}
 * 	
 * 	public function filter_not_blocked2($blocked){
 * 		$this->setIteratorClass('DsFilter_ExampleNotBlocked');
 * 		$iterator = $this->getIterator();
 * 		$iterator->set_block_list($blocked);
 * 		return $iterator;
 * 	}
 * }
 * 
 * class DsFilter_ExampleNotBlocked extends FilterIterator{
 * 	protected $blocked;
 * 	
 * 	public function set_block_list($list){
 * 		$this->blocked = $list;
 * 	}
 * 	
 * 	//implements the abstract method of FilterIterator
 * 	public function accept() {
 * 		return !in_array($this->current()->id, $this->blocked);
 * 	}
 * }
 * </code>
 * @author Rodin <luodan@staff.sina.com.cn>
 *
 */
abstract class Comm_DataObject_Set extends ArrayObject{
	protected $child_class = 'Comm_DataObject';
	protected $class_name = __CLASS__;
	protected $mode;
	
	static protected $object_storage = array(); 
	
	/**
	 * 清除指定类的对象缓存中的所有对象
	 * 
	 * @param string $type 类名。可选，采用null为默认值。若为空，则清除所有缓存的对象。若指定的type不存在，则抛出一个异常。
	 * @throws Comm_Dataobject_Exception
	 */
	static public function clear_cached_objects($type = null){
		if(is_null($type)){
			foreach (self::$object_storage as $type => $data){
				self::$object_storage[$type] = null;
				$data = null;
			}
			self::$object_storage = array();
		}elseif (isset(self::$object_storage[$type])){
			self::$object_storage[$type] = array();
		}else{
			throw new Comm_Dataobject_Exception('non exist type');
		}
	}
	
	public function __construct($init_data = NULL, $mode = Comm_DataObject::MODE_INPUT){
		$this->set_dataobject_mode($mode);
		if($init_data){
			foreach ($init_data as $value){
				parent::append($value);
			}
		}
		$this->class_name = get_class($this);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayObject::offsetSet()
	 */
	public function offsetSet($index, $value){
		if(is_null($value)){
			parent::offsetUnset($index);
			return ;
		}
		
		if(!is_object($value) || !($value instanceof $this->child_class)){
			$value = $this->create_object($value);
		}
		
		parent::offsetSet($index, $value);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayObject::offsetGet()
	 */
	public function offsetGet($index){
		$value = parent::offsetGet($index);
		return $value;
	}
	
	/**
	 * 
	 * 删除所有子对象并解除引用
	 */
	public function clear(){
		if($this->count()){
			$keys = array_keys($this->getArrayCopy());
			foreach ($keys as $k){
				parent::offsetSet($k, NULL);
				//maybe we should clear object cache here
			}
			$this->exchangeArray(array());
		}
	}
	
	/**
	 * 获取数据对象类型
	 * @return enum Comm_DataObject::MODE_*系列常量
	 */
	public function get_dataobject_mode(){
		return $this->mode;
	}
	
	/**
	 * 设置数据对象类型
	 * 
	 * @param enum $mode Comm_DataObject::MODE_*系列常量
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
		$array = $this->getArrayCopy();
		if (!$recursive){
			return $array;
		}
		foreach ($array as $k => $v){
			if(is_object($v) && method_exists($v, 'to_array')){
				$array[$k] = $v->to_array($recursive);
			}
		}
		return $array;
	}
	
	public function __destruct(){
		$this->clear();
	}	
	
	static protected function get_cached_object($type, $id){
		if(isset(self::$object_storage[$type]) && isset(self::$object_storage[$type][$id])){
			return self::$object_storage[$type][$id];
		}
		return null;
	}
	
	static protected function save_object($type, $id, $object){
		$original = null;
		if(!isset(self::$object_storage[$type])){
			self::$object_storage[$type] = array();
		}
		
		if(isset(self::$object_storage[$type][$id])){
			$original = self::$object_storage[$type][$id];
		}
		self::$object_storage[$type][$id] = $object;
		return $original;
	}

	protected function create_object($value){
		$id = $this->get_object_id($value);
		$class = $this->child_class;
		
		if($id === NULL){
			return new $class($value, $this->mode);
		}
		
		$object = self::get_cached_object($this->class_name, $id);
		if(!$object){
			$object = new $class($value, $this->mode);
			self::save_object($this->class_name, $id, $object);
			return $object;
		}
		
		foreach ($value as $k => $v){
			$object->$k = $v;
		}
		
		return $object;
	}
	
	/**
	 * 从指定的数据生成对象id。
	 * 
	 * @param mixed $value 数据
	 * @return mixed|null 对象id。如果返回null，则不会使用对象缓存，而是每次请求重新创建一个新的对象。
	 */
	abstract protected function get_object_id($value);
}

