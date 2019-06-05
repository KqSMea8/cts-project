<?php 

/**
 * 渲染器的基类
 * 
 * @author Rodin <luodan@staff.sina.com.cn>
 * @package Swift
 * @subpackage Bigpipe
 */
abstract class Comm_Bigpipe_Render{
	static protected $template_engine_class = '';
	
	/**
	 * 当前需要渲染的根Pagelet
	 * 
	 * @var Comm_Bigpipe_Pagelet
	 */
	protected $pl;
	
	/**
	 * 元数据链
	 * 
	 * @var array
	 */
	protected $meta_data_chain = array();
	
	protected $exceptions = array();
	
	/**
	 * 设置模板引擎的类名。
	 * 
	 * @param string $template_engine_class 类名。必须实现 Comm_Template_Interface
	 * @throws Comm_Bigpipe_Exception
	 */
	static public function set_template_engine_class($template_engine_class){
		$class_reflection = new ReflectionClass($template_engine_class);
		if(!$class_reflection->implementsInterface('Comm_Template_Interface')){
			throw new Comm_Bigpipe_Exception('Template class name should implements Comm_Template_Interface');
		}
		self::$template_engine_class = $template_engine_class;
	}
	
	static public function detect_render_type(){
	    $info = Comm_ClientProber::get_client_agent();
        if (isset($info['browser']) && $info['browser'] && $info['browser'] == 'Internet Explorer') {
        	//When IE evolved to ver 10.0, should update this.
            if (!strpos(Comm_ClientProber::$user_agent, "MSIE 9.0") && !strpos(Comm_ClientProber::$user_agent, "MSIE 8.0") && !strpos(Comm_ClientProber::$user_agent, "MSIE 7.0")) {
	            return 'Traditional';
            }
        }
		
		if(isset($_GET['nojs']) && $_GET['nojs']){
			return 'Traditional';
		}
		if(isset($_GET['ajaxpagelet']) && $_GET['ajaxpagelet']){
			return 'ScriptOnlyStreamline';
		}
		return 'Streamline';
	}
	
	/**
	 * 根据指定的$render_type来创建render。
	 * 
	 * 如果是Swift自带的渲染器，可以不带Comm_Bigpipe_的前缀和"Render"后缀；否则必须包含其全部类名。
	 * 例如：
	 *   //以下是合法的：
	 *   Comm_Bigpipe_TraditionalRender
	 *   Comm_Bigpipe_StreamlineRender
	 *   Traditional //等同于 Comm_Bigpipe_TraditionalRender
	 *   Streamline  //等同于 Comm_Bigpipe_StreamlineRender
	 *   Project_Bigpipe_Custom_Render  //合法的自定义Render
	 * 
	 * @param string $render_type 指定的渲染器类型。必须是 Comm_Bigpipe_Render的派生类。
	 * @param Comm_Bigpipe_Pagelet $pl 要渲染的pagelet。可选，默认为null。但调用Comm_Bigpipe_Render::render()方法之前必须设置。
	 * @return Comm_Bigpipe_Render 指定的渲染器
	 * @throws Comm_Bigpipe_Exception
	 */
	final static public function create($render_type, Comm_Bigpipe_Pagelet $pl = null){
		$render_class = 'Comm_Bigpipe_' . ucfirst($render_type) . 'Render';
		if(!class_exists($render_class) || !is_subclass_of($render_class, __CLASS__)){
			if(class_exists($render_type) && is_subclass_of($render_type, __CLASS__)){
				$render_class = $render_type;
			}else{
				throw new Comm_Bigpipe_Exception('Invalid render class:' . $render_type);
			}
		}

		return new $render_class($pl);
	}
	
	/**
	 * 深度优先遍历
	 * 
	 * benchmark: 5.3.3, 2.32 basepoint (smaller is faster)
	 * @param mixed $root
	 * @param callback $callback_enter
	 * @param callback $callback_leave
	 */
	static public function dfs($root, $callback_enter, $callback_leave){
		if(is_callable($callback_enter)){
			call_user_func($callback_enter, $root);
		}
		foreach ($root->getIterator() as $node){
			self::dfs($node, $callback_enter, $callback_leave);
		}
		if(is_callable($callback_leave)){
			call_user_func($callback_leave, $root);
		}
	}
	
	/**
	 * 深度优先遍历(非递归调用+数组模拟栈实现)
	 * 
	 * benchmark: 5.3.3, 3.29 basepoint (smaller is faster)
	 * @param mixed $root
	 * @param callback $callback_enter
	 * @param callback $callback_leave
	 * @todo auto rewind the iterator
	 */
	static public function dfs_arraystack($root, $callback_enter, $callback_leave){
		$cur = $root;
		$stack = array();
		$is_back = false;
		is_callable($callback_enter) OR $callback_enter = '';
		is_callable($callback_leave) OR $callback_leave = '';
		while($cur){
			!$is_back && $callback_enter AND call_user_func($callback_enter, $cur);
			list($index, $node)  = each($cur->getIterator());
			if($node){//enter child
				$is_back = false;
				array_push($stack, $cur);
				$cur = $node;
			}else{
				$is_back = true;
				$callback_leave AND call_user_func($callback_leave, $cur);
				$cur = $stack ? array_pop($stack) : null;
			}
		}
	}
	
	/**
	 * 深度优先遍历(非递归调用+SplStack实现)
	 * benchmark: 5.3.3, 3.58 basepoint (smaller is faster)
	 * @param mixed $root
	 * @param callback $callback_enter
	 * @param callback $callback_leave
	 * @todo auto rewind the iterator
	 */
	static public function dfs_stack($root, $callback_enter, $callback_leave){
		$cur = $root;
		$stack = new SplStack();
		$is_back = false;
		is_callable($callback_enter) OR $callback_enter = '';
		is_callable($callback_leave) OR $callback_leave = '';
		
		while($cur){
			!$is_back && $callback_enter AND call_user_func($callback_enter, $cur);
			list($index, $node) = each($cur->getIterator());
			if($node){//enter child
				$is_back = false;
				$stack->push($cur);
				$cur = $node;
			}else{
				$is_back = true;
				$callback_leave AND call_user_func($callback_leave, $cur);
				$cur = !$stack->isEmpty() ? $stack->pop() : null;
			}
		}
	}
	
	public function __construct(Comm_Bigpipe_Pagelet $pl = null){
		$this->pl = $pl;
	}
	
	/**
	 * 
	 * @param Comm_Bigpipe_Pagelet $pl
	 * @return Comm_Bigpipe_Render
	 */
	public function set_pagelet(Comm_Bigpipe_Pagelet $pl){
		$this->pl = $pl;
		return $this;
	}
	
	/**
	 * @return Comm_Bigpipe_Pagelet 
	 */
	public function get_pagelet(){
		return $this->pl;
	}
	
	public function prepare(){
	} 
	
	public function render(){
		if(!self::$template_engine_class){
			throw new Comm_Bigpipe_Exception('Must set a template engine class first');
		}
		$this->prepare();
		self::dfs($this->pl, array($this, 'enter'), array($this, 'leave'));
		$this->closure();
	}
	
	public function closure(){
	}
	
	static protected function render_pagelet_with_json(Comm_Bigpipe_Pagelet $pl, Comm_Template_Interface $tpl_engine){
		return json_encode(array('pid' => $pl->get_name(), 'js' => $pl->get_depends_scripts(), 'css' => $pl->get_depends_styles(), 'html' => $tpl_engine->fetch($pl->get_template())));
	}
	
	static protected function flush(){
		if(ob_get_level()){
			ob_flush();
		}
		flush();
	}
	
	static protected function assign_meta_chain_to_template(Comm_Template_Interface $tpl, array $meta_data_chain){
		foreach ($meta_data_chain as $meta){
			if($meta){
				$tpl->assign($meta);
			}
		}
	}
	
	abstract protected function enter(Comm_Bigpipe_Pagelet $node);
	
	abstract protected function leave(Comm_Bigpipe_Pagelet $node);
	
	protected function collect_exception(Exception $exception){
		$this->exceptions[] = $exception;
	}
	
	protected function process_exceptions(){
		if($this->exceptions){
			throw $this->exceptions[0];
		}
	}
	
	/**
	 * 
	 * @return Comm_Template_Interface
	 */
	protected function get_template_engine(){
		return new self::$template_engine_class();
	}
} 