<?php 

/**
 * 真正的bigpipe渲染模式
 * 
 * 尚未实现
 * @author Rodin <luodan@staff.sina.com.cn>
 * @package Swift
 * @subpackage Bigpipe
 */
class Comm_Bigpipe_RandomRender extends Comm_Bigpipe_Render{
	protected function enter(Comm_Bigpipe_Pagelet $pl){
		$data = $pl->prepare_data($is_Async = true);
		//register needed data in async data obtainer
	}
	
	protected function leave(Comm_Bigpipe_Pagelet $pl){
	}
	
	public function callback(Comm_Bigpipe_Pagelet $pl, $data){
		$tpl_engine = $this->get_template_engine();
		$tpl_engine->assign($data);
		if($pl->is_skeleton()){
			$tpl_engine->assign('pagelet_scripts', $pl->get_depends_scripts());
			$tpl_engine->assign('pagelet_styles', $pl->get_depends_styles());
			$tpl_engine->display($pl->get_template());
		}else{
			echo '<script>pageletM.render(' . self::render_pagelet_with_json($pl, $tpl_engine)  . ')</script>';
		}
		self::flush();
	}
}