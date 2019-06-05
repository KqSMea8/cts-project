<?php 

/**
 * 原始页面模式输出
 * @author Rodin <luodan@staff.sina.com.cn>
 * @package Swift
 * @subpackage Bigpipe
 */
class Comm_Bigpipe_TraditionalRender extends Comm_Bigpipe_Render{
	protected $scripts = array();
	protected $styles = array();
	protected $pl_contents = array();
	
	protected $exceptions = array();
	
	protected function enter(Comm_Bigpipe_Pagelet $pl){
		$this->meta_data_chain[] = $pl->get_meta_data();
		$this->scripts = array_merge($this->scripts, $pl->get_depends_scripts());
		$this->styles = array_merge($this->styles, $pl->get_depends_styles());
	}
	
	protected function leave(Comm_Bigpipe_Pagelet $pl){
		$tpl_engine = $this->get_template_engine();
		
		self::assign_meta_chain_to_template($tpl_engine, $this->meta_data_chain);
		
		try{
			$tpl_engine->assign($pl->prepare_data());
		}catch (Exception $ex){
			$this->collect_exception($ex);
			if($pl === $this->pl){
				//output blank
			}else{
				$this->pl_contents[$pl->get_name()] = '';
			}
			return ;
		}
		$tpl_engine->assign('pagelets', $this->pl_contents);
		if($pl === $this->pl){
			$tpl_engine->assign('pagelet_scripts', $this->scripts);
			$tpl_engine->assign('pagelet_styles', $this->styles);
			$tpl_engine->display($pl->get_template());
		}else{
			$this->pl_contents[$pl->get_name()] = $tpl_engine->fetch($pl->get_template());
		}
		//pop meta chain.
		array_pop($this->meta_data_chain);
	}
	
	/* (non-PHPdoc)
	 * @see Comm_Bigpipe_Render::closure()
	 */
	public function closure(){
		$this->process_exceptions();
	}
}