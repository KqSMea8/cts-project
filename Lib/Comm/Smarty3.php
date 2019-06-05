<?php

require_once T3PPATH.'/Smarty-3.0.7/libs/Smarty.class.php';

class Comm_Smarty3 extends Smarty implements Comm_Template_Interface{
    public function __construct(){
        parent::__construct();
        
        $this->compile_dir = Comm_Util::conf("env.cache_dir") . "/tplcompile";
        $this->cache_dir = Comm_Util::conf("env.cache_dir") . "/tplcache";
        $this->template_dir = APPPATH."/tpls";
        
        $this->left_delimiter = "<?";
        $this->right_delimiter = "?>";
    }
    
    public function clear_all_assign(){
        $this->clearAllAssign();
    }
}