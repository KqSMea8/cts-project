<?php 
/**
 * 模板引擎适配器接口
 * 
 * @author Rodin <luodan@staff.sina.com.cn>
 */
interface Comm_Template_Interface{
	public function assign($key, $value = null);
	
	public function fetch($template);
	
	public function display($template);
	
	public function clear_all_assign(); 
}