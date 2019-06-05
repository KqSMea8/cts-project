<?php

/**
 * 重构后的Bigpipe的pagelet的基类
 * 
 * @author Rodin <luodan@staff.sina.com.cn>
 * @package Swift
 * @subpackage Bigpipe
 */
class Comm_Bigpipe_Pagelet implements ArrayAccess,IteratorAggregate {
	static private $pagelet_names = array();
	
	private $name = '';
	protected $children = array ();
	protected $data = array ();
	protected $tpl = '';
	protected $scripts = array();
	protected $styles = array();
	protected $is_skeleton = false;
	protected $iterator;
	
	public function __construct($name, array $children = array()) {
		$this->set_name($name);
		foreach ($children as $child){
			$this->add_child($child);
		}
		return;
	}
	
	public function get_class_name() {
		return strtolower ( get_class($this) );
	}
	
	public function get_name(){
		return $this->name;
	}
	
	public function is_skeleton(){
		return $this->is_skeleton;
	}
	
	public function add_child(Comm_Bigpipe_Pagelet $child){
		if($this->offsetExists($child->get_name())){
			throw new Comm_Bigpipe_Exception('pl added already');
		}
		$this->offsetSet($child->get_name(), $child);
	}
	
	public function get_child($name) {
		return $this->offsetGet($name);
	}
	
	public function get_children(){
		return $this->children;
	}
	
	public function get_children_names(){
		return array_keys($this->children);
	}
	
	public function del_child($name){
		return $this->offsetUnset($name);
	}
	
	public function get_template() {
		if(!$this->tpl){
			throw new Comm_Bigpipe_Exception('tpl not set');
		}
		return $this->tpl;
	}
	
	public function get_meta_data(){
	}
	
	public function prepare_data($is_asynchronous = false) {
	}
	
	public function get_depends_scripts() {
		return $this->scripts;
	}
	
	public function get_depends_styles() {
		return $this->styles;
	}
	
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		return isset($this->children[$offset]);
	}
	
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		return $this->offsetExists($offset) ? $this->children[$offset] : null;
	}
	
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		if(!$this->is_skeleton() && $value->is_skeleton()){
			throw new Comm_Bigpipe_Exception('Skeleton pagelet cannot added to a non-skeleton parent');
		}
		return $this->children[$offset] = $value;
	}
	
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		unset($this->children[$offset]);
	}
	
	/**
	 * Alias of Comm_Bigpipe_Pagelet::get_iterator()
	 * 
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator($force_new = false) {
		return $this->get_iterator($force_new);
	}
	
	/**
	 * @param bool $force_new 是否更新
	 */
	public function get_iterator($force_new = false){
		if($this->iterator === NULL || $force_new){
			$this->iterator = new ArrayIterator($this->children);
		}
		return $this->iterator;
	}
	
	public function rewind_iterator($recursive = false){
		if(!$this->iterator){
			return;
		}
		$this->iterator->rewind();
		if($recursive){
			foreach ($this->iterator as $child){
				$child->getIterator()->rewind_iterator($recursive);
			}
			$this->iterator->rewind();
		}
		return;
	}
	
	private function set_name($name){
		//name should be unique
		if(isset(self::$pagelet_names[$name])){
			throw new Comm_Bigpipe_Exception('pagelets name cannot be dunplicated');
		}
		self::$pagelet_names[$name] = $name;
		$this->name = $name;
	}
	
	/**
	 * debug 调试输出结果
	 * @param unknown $info
	 */
	public static function debug ( $info )
	{
	    echo '<pre>';print_r($info);exit;
	}
}