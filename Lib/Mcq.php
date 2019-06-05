<?php

class Lib_Mcq extends Memcached {
    
    
    private $alias = '';  //alias和mcq的key应保持一致
    
    public function __construct($alias) {
        $config = Lib_Config::get("resource.mcq.$alias"); 
        
        $this->alias = $alias;
        parent::__construct();

        list($addr, $port) = explode(':', $config, 2);
        
        if (empty($addr) || empty($port)) {
            throw new Exception('Memached Config Error:'.$config); 
	}
        
        $this->addServer($addr, $port);
        $this->setOption(Memcached::OPT_NO_BLOCK, true);
        $this->setOption(Memcached::OPT_CONNECT_TIMEOUT, 200);
        $this->setOption(Memcached::OPT_POLL_TIMEOUT, 50);
        return;
    }
    
    public function upload($content) {
        $content = json_encode($content);
        return $this->set($this->alias, $content);
    }
    
    public function download() {
        $content = $this->get($this->alias);
        $content = json_decode($content, true);
        
        return $content;
    }
}
