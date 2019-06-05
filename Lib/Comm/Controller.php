<?php
/**
 * 存放controller抽象类
 * 
 * @author Rodin <luodan@staff.sina.com.cn>
 */

/**
 * 抽象的Controller
 *   子类必须实现run方法
 */
abstract class Comm_Controller {
	/**
	 * 构造方法设置了final属性以防止子类覆盖
	 * 
	 */
	final public function __construct() {
		Comm_Context::init();
		$this->init();
	}
	
	/**
	 * 子类的初始化需要在这里覆盖实现
	 */
	protected function init(){
	} 
	
	/**
	 * 业务逻辑
	 */
	abstract public function run();
	
	/**
	 * 
	 * 子类的销毁需要在这里覆盖实现
	 */
	protected function destroy(){
	}
	
	final public function __destruct() {
		try{
			$this->destroy();
		}catch (Exception $ex){
			//we just hope the destroy runs correctly.
		}
		
		try{
			Comm_Context::clear();
		}catch (Exception $ex){
		}
	}
}
