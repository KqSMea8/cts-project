<?php 

/**
 * 流水线方式输出
 * @author Rodin <luodan@staff.sina.com.cn>
 * @package Swift
 * @subpackage Bigpipe
 */
class Comm_Bigpipe_StreamlineRender extends Comm_Bigpipe_Render{
	protected $defered_pagelets = array();
	protected $scripts = array();
	protected $skeleton_scripts = array();
	protected $styles = array();
	protected $pl_contents = array();
	
	protected function enter(Comm_Bigpipe_Pagelet $pl){
		$this->meta_data_chain[] = $pl->get_meta_data();
		if(!$pl->is_skeleton()){
			$this->defered_pagelets[] = array($pl, $this->meta_data_chain);
			return;
		}
		
		$pl->get_depends_scripts() AND $this->skeleton_scripts[$pl->get_name()] = $pl->get_depends_scripts();
		$this->scripts = array_merge($this->scripts, $pl->get_depends_scripts());
		$this->styles = array_merge($this->styles, $pl->get_depends_styles());
	}
	
	protected function leave(Comm_Bigpipe_Pagelet $pl){
		if($pl->is_skeleton()){
			$this->render_skeleton_pagelet($pl);
		}
		// meta_data只会对自己的子pl（自己的叶节点）共享（多级继承），而不会影响其它
		array_pop($this->meta_data_chain);
	}
	
	/**
	 * 从html中删除</html>结束标签。
	 * 
	 * 如果html结束标签出现在尾部(最后的20字节之内)，则移除之。否则，会保留，以防止替换掉不该替换的标签。
	 * 
	 * @param string $html
	 * @return string
	 */
	protected function move_out_html_close_tag($html){
		$html_close_tag_pos = strripos($html, '</html>');
		if($html_close_tag_pos !== false && abs(strlen($html) - $html_close_tag_pos) <= 20){
			$html = substr_replace($html, '', $html_close_tag_pos, 7);
		}
		return $html;
	}
	
	protected function render_skeleton_pagelet(Comm_Bigpipe_Pagelet $pl){
		$children = array_fill_keys($pl->get_children_names(), '');
		
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
		$tpl_engine->assign('pagelets', array_merge($children, $this->pl_contents));
		// ==时为根节点，反之非根节点
		if($pl === $this->pl){
			$tpl_engine->assign('pagelet_scripts', $this->scripts);
			$tpl_engine->assign('pagelet_styles', $this->styles);
			$html = $tpl_engine->fetch($pl->get_template());
			echo $this->move_out_html_close_tag($html);
			self::flush();
		}else{
			$this->pl_contents[$pl->get_name()] = $tpl_engine->fetch($pl->get_template());
		}
	}
	
	protected function render_stream_pagelet(Comm_Bigpipe_Pagelet $pl, $meta_data_chain){
		//防止模板里出现undefined index
		$children = array_fill_keys($pl->get_children_names(), '');
		
		$tpl_engine = $this->get_template_engine();
		
		self::assign_meta_chain_to_template($tpl_engine, $meta_data_chain);
		try{
			$tpl_engine->assign($pl->prepare_data());
			$tpl_engine->assign('pagelets', $children);
			$tpl_engine->assign('pagelet_scripts', $pl->get_depends_scripts());
			$tpl_engine->assign('pagelet_styles', $pl->get_depends_styles());
			echo $this->surround_with_script_tag(self::render_pagelet_with_json($pl, $tpl_engine));
			self::flush();
		}catch (Exception $ex){
			$this->collect_exception($ex);
		}
	}
	
    protected function render_script_with_json($pl_name, $scripts){
        return json_encode(array('pid' => $pl_name, 'js' => $scripts));
    }
	
	protected function render_skeleton_scripts(){
	    foreach($this->skeleton_scripts as $pl_name => $scripts){
	        echo $this->surround_with_script_tag($this->render_script_with_json($pl_name, $scripts));
	    }
	    self::flush();
	}
	
	protected function render_defered_pagelets(){
		while ($this->defered_pagelets){
			list($pl, $meta_data_chain) = array_shift($this->defered_pagelets);
			$this->render_stream_pagelet($pl, $meta_data_chain);
		}
	}
	
	protected function surround_with_script_tag($string){
		return "<script>STK && STK.pageletM && STK.pageletM.view({$string})</script>\n";
	}
	
	/**
	 * bigpipe的streamline方式最后处理需要顺序输出的pagelets
	 * 
	 * (non-PHPdoc)
	 * @see Comm_Bigpipe_Render::closure()
	 */
	public function closure(){
	    $this->render_skeleton_scripts();
		$this->render_defered_pagelets();
		//because we had moved the html close tag away, so we need patch it up finally.
		echo "</html>";
		$this->process_exceptions();
	}
}