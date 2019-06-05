<?php

require_once T3PPATH.'/Smarty-2.6.26/libs/Smarty.class.php';

class Comm_Smarty2 extends Smarty implements Comm_Template_Interface{
    
    public function __construct(){
        $this->smarty = new Smarty();
        
        $this->compile_dir = Comm_Util::conf("env.cache_dir") . "/tplcompile";
        $this->cache_dir = Comm_Util::conf("env.cache_dir") . "/tplcache";
        $this->template_dir = APPPATH."/tpls";
        
        $this->left_delimiter = "<?";
        $this->right_delimiter = "?>";
    }
}